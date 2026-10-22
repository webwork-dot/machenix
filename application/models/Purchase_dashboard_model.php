<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get Consolidated Purchase Overview (Import + Local, White & Black Breakdown)
     */
    public function get_purchase_overview_summary($company_id, $from_date, $to_date)
    {
        $local  = $this->get_local_purchase_summary($company_id, $from_date, $to_date);
        $import = $this->get_import_purchase_summary($company_id, $from_date, $to_date);
        $exp    = $this->get_po_expense_summary($company_id, $from_date, $to_date);
        $pay    = $this->get_supplier_payments_summary($company_id, $from_date, $to_date);

        $gross_white = (float)$local['white_purchase_amount'] + (float)$import['white_purchase_amount'];
        $gross_black = (float)$local['black_purchase_amount'] + (float)$import['black_purchase_amount'];
        $gross_total = $gross_white + $gross_black;

        $white_pct = ($gross_total > 0) ? round(($gross_white / $gross_total) * 100, 1) : 0;
        $black_pct = ($gross_total > 0) ? round(($gross_black / $gross_total) * 100, 1) : 0;

        $gross_usd = (float)($import['total_usd'] ?? $import['actual_usd'] ?? 0) + (float)($local['total_usd'] ?? 0);
        $gross_rmb = (float)($import['total_rmb'] ?? $import['actual_rmb'] ?? 0) + (float)($local['total_rmb'] ?? 0);
        $white_usd = (float)($import['white_usd'] ?? 0);
        $black_usd = (float)($import['black_usd'] ?? 0);
        $white_rmb = (float)($import['white_rmb'] ?? 0);
        $black_rmb = (float)($import['black_rmb'] ?? 0);

        return [
            'total_orders'          => (int)$local['total_orders'] + (int)$import['total_orders'],
            'white_purchase_amount' => $gross_white,
            'black_purchase_amount' => $gross_black,
            'gross_purchase_amount' => $gross_total,
            'total_usd'             => $gross_usd,
            'total_rmb'             => $gross_rmb,
            'white_usd'             => $white_usd,
            'black_usd'             => $black_usd,
            'white_rmb'             => $white_rmb,
            'black_rmb'             => $black_rmb,
            'white_percent'         => $white_pct,
            'black_percent'         => $black_pct,
            'total_basic_value'     => (float)$local['total_basic_value'],
            'total_gst'             => (float)$local['total_gst'] + (float)$import['customs_tax_amount'],
            'total_po_expense'      => (float)$exp['gross_expense_amount'],
            'total_payments_made'   => (float)$pay['gross_payment_amount'],
            'local_summary'         => $local,
            'import_summary'        => $import,
            'expense_summary'       => $exp,
            'payment_summary'       => $pay,
        ];
    }

    /**
     * Get Local Purchase Performance Summary (White, Black, Basic, GST & Quantities)
     */
    public function get_local_purchase_summary($company_id, $from_date, $to_date)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND po.company_id = '{$company_id}'" : "";

        // Query Local Purchase Orders
        $sql = "SELECT 
                    COUNT(DISTINCT po.id) as total_orders,
                    IFNULL(SUM(po.basic_value), 0) as total_basic_value,
                    IFNULL(SUM(po.cgst_amount + po.sgst_amount + po.igst_amount), 0) as total_gst,
                    IFNULL(SUM(po.grand_total), 0) as white_purchase_amount,
                    IFNULL(SUM(po.total_black_amount_summary), 0) as black_purchase_amount,
                    IFNULL(SUM(po.grand_total + po.total_black_amount_summary), 0) as gross_purchase_amount
                FROM purchase_order po
                WHERE po.is_deleted = '0'
                  AND po.method IN ('local', 'company')
                  {$comp_filter}
                  AND (DATE(po.date) BETWEEN '{$from_date}' AND '{$to_date}')";

        $res = $this->db->query($sql)->row_array();

        $white_amt = (float)($res['white_purchase_amount'] ?? 0);
        $black_amt = (float)($res['black_purchase_amount'] ?? 0);
        $gross_amt = (float)($res['gross_purchase_amount'] ?? ($white_amt + $black_amt));

        return [
            'total_orders'          => (int)($res['total_orders'] ?? 0),
            'total_basic_value'     => (float)($res['total_basic_value'] ?? 0),
            'total_gst'             => (float)($res['total_gst'] ?? 0),
            'white_purchase_amount' => $white_amt,
            'black_purchase_amount' => $black_amt,
            'gross_purchase_amount' => $gross_amt,
            'total_usd'             => 0.00,
            'total_rmb'             => 0.00,
            'white_usd'             => 0.00,
            'black_usd'             => 0.00,
            'white_rmb'             => 0.00,
            'black_rmb'             => 0.00,
        ];
    }

    /**
     * Get Import Purchase Summary (White INR, Black INR, USD, RMB & Customs Duty)
     */
    public function get_import_purchase_summary($company_id, $from_date, $to_date)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND po.company_id = '{$company_id}'" : "";

        // Total Import POs count
        $po_sql = "SELECT 
                        COUNT(DISTINCT po.id) as total_orders
                   FROM purchase_order po
                   WHERE po.is_deleted = '0'
                     AND po.method = 'import'
                     {$comp_filter}
                     AND (DATE(po.date) BETWEEN '{$from_date}' AND '{$to_date}')";

        $po_res = $this->db->query($po_sql)->row_array();

        // 1. Purchase In Received Products (Actual Invoiced Shipments)
        $pip_sql = "SELECT 
                        IFNULL(SUM(pip.official_total_rs), 0) as white_import_inr,
                        IFNULL(SUM(CASE WHEN pip.black_total_price > 0 THEN pip.black_total_price 
                                        ELSE ((pip.actual_qty * pip.actual_inr) - pip.official_total_rs) END), 0) as black_import_inr,
                        IFNULL(SUM(pip.total_amount_usd), 0) as white_usd,
                        IFNULL(SUM(pip.actual_qty * pip.actual_usd), 0) as actual_usd,
                        IFNULL(SUM(pip.official_ci_qty * pip.unit_price_rmb), 0) as white_rmb,
                        IFNULL(SUM(pip.actual_qty * pip.actual_rmb), 0) as actual_rmb,
                        IFNULL(SUM(pip.duty_amt + pip.gst_amt), 0) as customs_tax_amount,
                        IFNULL(SUM(pip.actual_qty), 0) as total_received_qty
                    FROM purchase_in_product pip
                    INNER JOIN purchase_order po ON po.id = pip.parent_id
                    WHERE po.is_deleted = '0'
                      AND po.method = 'import'
                      {$comp_filter}
                      AND (DATE(po.date) BETWEEN '{$from_date}' AND '{$to_date}')";

        $pip_res = $this->db->query($pip_sql)->row_array();

        $white_inr = (float)($pip_res['white_import_inr'] ?? 0);
        $black_inr = max(0, (float)($pip_res['black_import_inr'] ?? 0));
        $gross_inr = $white_inr + $black_inr;

        // If no purchase_in received rows yet, fallback to purchase_order_product
        if ($gross_inr == 0) {
            $pop_sql = "SELECT 
                            IFNULL(SUM(pop.total_val), 0) as white_import_inr,
                            IFNULL(SUM(pop.black_amt_total), 0) as black_import_inr,
                            IFNULL(SUM(pop.quantity), 0) as total_received_qty
                        FROM purchase_order_product pop
                        INNER JOIN purchase_order po ON po.id = pop.parent_id
                        WHERE po.is_deleted = '0'
                          AND po.method = 'import'
                          {$comp_filter}
                          AND (DATE(po.date) BETWEEN '{$from_date}' AND '{$to_date}')";
            $pop_res = $this->db->query($pop_sql)->row_array();
            $white_inr = (float)($pop_res['white_import_inr'] ?? 0);
            $black_inr = (float)($pop_res['black_import_inr'] ?? 0);
            $gross_inr = $white_inr + $black_inr;
        }

        $white_usd = (float)($pip_res['white_usd'] ?? 0);
        $act_usd   = (float)($pip_res['actual_usd'] ?? 0);
        $white_rmb = (float)($pip_res['white_rmb'] ?? 0);
        $act_rmb   = (float)($pip_res['actual_rmb'] ?? 0);

        return [
            'total_orders'          => (int)($po_res['total_orders'] ?? 0),
            'white_purchase_amount' => $white_inr,
            'black_purchase_amount' => $black_inr,
            'gross_purchase_amount' => $gross_inr,
            'total_usd'             => $act_usd,
            'total_rmb'             => $act_rmb,
            'white_usd'             => $white_usd,
            'actual_usd'            => $act_usd,
            'black_usd'             => max(0, $act_usd - $white_usd),
            'white_rmb'             => $white_rmb,
            'actual_rmb'            => $act_rmb,
            'black_rmb'             => max(0, $act_rmb - $white_rmb),
            'customs_tax_amount'    => (float)($pip_res['customs_tax_amount'] ?? 0),
            'total_qty'             => (float)($pip_res['total_received_qty'] ?? 0),
        ];
    }

    /**
     * Get PO Operational Expenses Summary (Official White vs Unofficial Black, Currencies)
     */
    public function get_po_expense_summary($company_id, $from_date, $to_date)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND e.company_id = '{$company_id}'" : "";

        $sql = "SELECT 
                    COUNT(e.id) as total_count,
                    IFNULL(SUM(CASE WHEN e.type = 'official' THEN e.sub_total ELSE 0 END), 0) as white_sub_total,
                    IFNULL(SUM(CASE WHEN e.type = 'official' THEN e.gst_total ELSE 0 END), 0) as white_gst_total,
                    IFNULL(SUM(CASE WHEN e.type = 'official' THEN e.grand_total ELSE 0 END), 0) as white_expense_amount,
                    IFNULL(SUM(CASE WHEN e.type IN ('unofficial', 'extras') THEN e.grand_total ELSE 0 END), 0) as black_expense_amount,
                    IFNULL(SUM(e.grand_total), 0) as gross_expense_amount,
                    IFNULL(SUM(CASE WHEN e.type = 'official' THEN e.usd ELSE 0 END), 0) as white_usd,
                    IFNULL(SUM(CASE WHEN e.type IN ('unofficial', 'extras') THEN e.usd ELSE 0 END), 0) as black_usd,
                    IFNULL(SUM(e.usd), 0) as total_usd,
                    IFNULL(SUM(CASE WHEN e.type = 'official' THEN e.rmb ELSE 0 END), 0) as white_rmb,
                    IFNULL(SUM(CASE WHEN e.type IN ('unofficial', 'extras') THEN e.rmb ELSE 0 END), 0) as black_rmb,
                    IFNULL(SUM(e.rmb), 0) as total_rmb
                FROM po_expense e
                WHERE e.is_delete = 0
                  {$comp_filter}
                  AND (DATE(e.expense_date) BETWEEN '{$from_date}' AND '{$to_date}')";

        $res = $this->db->query($sql)->row_array();

        $white_exp = (float)($res['white_expense_amount'] ?? 0);
        $black_exp = (float)($res['black_expense_amount'] ?? 0);
        $gross_exp = (float)($res['gross_expense_amount'] ?? ($white_exp + $black_exp));

        return [
            'total_count'          => (int)($res['total_count'] ?? 0),
            'white_sub_total'      => (float)($res['white_sub_total'] ?? 0),
            'white_gst_total'      => (float)($res['white_gst_total'] ?? 0),
            'white_expense_amount' => $white_exp,
            'black_expense_amount' => $black_exp,
            'gross_expense_amount' => $gross_exp,
            'total_usd'            => (float)($res['total_usd'] ?? 0),
            'total_rmb'            => (float)($res['total_rmb'] ?? 0),
            'white_usd'            => (float)($res['white_usd'] ?? 0),
            'black_usd'            => (float)($res['black_usd'] ?? 0),
            'white_rmb'            => (float)($res['white_rmb'] ?? 0),
            'black_rmb'            => (float)($res['black_rmb'] ?? 0),
        ];
    }

    /**
     * Get Supplier Payments Summary (Local & Import Suppliers, type = 'payment', Official vs Unofficial, Currencies)
     */
    public function get_supplier_payments_summary($company_id, $from_date, $to_date)
    {
        $company_id = (int)$company_id;
        $comp_where = !empty($company_id) ? " AND (p.company_id = '{$company_id}' OR s.company_id = '{$company_id}')" : "";

        $sql = "SELECT 
                    p.id,
                    p.payment_type,
                    LOWER(p.payment_method) as payment_method,
                    s.type as supplier_type,
                    p.amount_rs as inr,
                    p.amount_dollar as usd,
                    p.amount_rmb as rmb
                FROM payments p
                JOIN supplier s ON p.supplier_id = s.id
                WHERE p.is_delete = 0
                  AND p.type = 'payment'
                  {$comp_where}
                  AND (DATE(p.payment_date) BETWEEN '{$from_date}' AND '{$to_date}')";

        $res = $this->db->query($sql)->result_array();

        $summary = [
            'total_count'          => 0,
            'gross_payment_amount' => 0.0,
            'gross_usd'            => 0.0,
            'gross_rmb'            => 0.0,
            'white_payment_amount' => 0.0,
            'black_payment_amount' => 0.0,
            'white_usd'            => 0.0,
            'black_usd'            => 0.0,
            'white_rmb'            => 0.0,
            'black_rmb'            => 0.0,
            'white_count'          => 0,
            'black_count'          => 0,
            'local_payments'       => [
                'total' => 0.0, 'white' => 0.0, 'black' => 0.0, 'count' => 0
            ],
            'import_payments'      => [
                'total' => 0.0, 'white' => 0.0, 'black' => 0.0,
                'usd' => 0.0, 'white_usd' => 0.0, 'black_usd' => 0.0,
                'rmb' => 0.0, 'white_rmb' => 0.0, 'black_rmb' => 0.0,
                'count' => 0
            ]
        ];

        foreach ($res as $row) {
            $is_white = ($row['payment_type'] === 'official' || $row['payment_method'] === 'cheque');
            $inr = (float)$row['inr'];
            $usd = (float)$row['usd'];
            $rmb = (float)$row['rmb'];
            $is_local = ($row['supplier_type'] === 'local');

            $summary['total_count']++;
            $summary['gross_payment_amount'] += $inr;
            $summary['gross_usd']            += $usd;
            $summary['gross_rmb']            += $rmb;

            if ($is_white) {
                $summary['white_count']++;
                $summary['white_payment_amount'] += $inr;
                $summary['white_usd']            += $usd;
                $summary['white_rmb']            += $rmb;
            } else {
                $summary['black_count']++;
                $summary['black_payment_amount'] += $inr;
                $summary['black_usd']            += $usd;
                $summary['black_rmb']            += $rmb;
            }

            if ($is_local) {
                $summary['local_payments']['count']++;
                $summary['local_payments']['total'] += $inr;
                if ($is_white) {
                    $summary['local_payments']['white'] += $inr;
                } else {
                    $summary['local_payments']['black'] += $inr;
                }
            } else {
                $summary['import_payments']['count']++;
                $summary['import_payments']['total']     += $inr;
                $summary['import_payments']['usd']       += $usd;
                $summary['import_payments']['rmb']       += $rmb;
                if ($is_white) {
                    $summary['import_payments']['white']     += $inr;
                    $summary['import_payments']['white_usd'] += $usd;
                    $summary['import_payments']['white_rmb'] += $rmb;
                } else {
                    $summary['import_payments']['black']     += $inr;
                    $summary['import_payments']['black_usd'] += $usd;
                    $summary['import_payments']['black_rmb'] += $rmb;
                }
            }
        }

        return $summary;
    }

    /**
     * Get Vendor Payments Summary (Operational / PO Expense Vendors, Official vs Unofficial, Currencies)
     */
    public function get_vendor_payments_summary($company_id, $from_date, $to_date)
    {
        $company_id = (int)$company_id;
        $comp_where = !empty($company_id) ? " AND (vp.company_id = '{$company_id}' OR v.company_id = '{$company_id}')" : "";

        $sql = "SELECT 
                    vp.id,
                    vp.payment_type,
                    LOWER(vp.payment_method) as payment_method,
                    vp.inr,
                    vp.usd,
                    vp.rmb
                FROM vendor_payments vp
                LEFT JOIN my_companies v ON vp.vendor_id = v.id
                WHERE vp.is_delete = 0
                  {$comp_where}
                  AND (DATE(vp.payment_date) BETWEEN '{$from_date}' AND '{$to_date}')";

        $res = $this->db->query($sql)->result_array();

        $summary = [
            'total_count'          => 0,
            'gross_payment_amount' => 0.0,
            'gross_usd'            => 0.0,
            'gross_rmb'            => 0.0,
            'white_payment_amount' => 0.0,
            'black_payment_amount' => 0.0,
            'white_usd'            => 0.0,
            'black_usd'            => 0.0,
            'white_rmb'            => 0.0,
            'black_rmb'            => 0.0,
            'white_count'          => 0,
            'black_count'          => 0,
        ];

        foreach ($res as $row) {
            $is_white = ($row['payment_type'] === 'official' || $row['payment_method'] === 'cheque');
            $inr = (float)$row['inr'];
            $usd = (float)$row['usd'];
            $rmb = (float)$row['rmb'];

            $summary['total_count']++;
            $summary['gross_payment_amount'] += $inr;
            $summary['gross_usd']            += $usd;
            $summary['gross_rmb']            += $rmb;

            if ($is_white) {
                $summary['white_count']++;
                $summary['white_payment_amount'] += $inr;
                $summary['white_usd']            += $usd;
                $summary['white_rmb']            += $rmb;
            } else {
                $summary['black_count']++;
                $summary['black_payment_amount'] += $inr;
                $summary['black_usd']            += $usd;
                $summary['black_rmb']            += $rmb;
            }
        }

        return $summary;
    }

    /**
     * Get Real-time Total Supplier Outstandings based on Ledger Logic
     * (Opening + Purchases - Payments + Adjustments, Local + Import, Official vs Unofficial, All Currencies)
     */
    public function get_supplier_outstanding_summary($company_id)
    {
        $company_id = (int)$company_id;
        $comp_where = !empty($company_id) ? " AND s.company_id = '{$company_id}'" : "";

        $suppliers = $this->db->query("SELECT id, type, name, outstanding_inr, outstanding_usd, outstanding_rmb FROM supplier s WHERE s.is_deleted = 0 {$comp_where}")->result_array();

        $summary = [
            'total_outstanding_inr'  => 0.0,
            'total_outstanding_usd'  => 0.0,
            'total_outstanding_rmb'  => 0.0,
            'official_outstanding'   => 0.0,
            'official_usd'           => 0.0,
            'official_rmb'           => 0.0,
            'unofficial_outstanding' => 0.0,
            'unofficial_usd'         => 0.0,
            'unofficial_rmb'         => 0.0,
            'local_outstanding_inr'  => 0.0,
            'local_official_inr'     => 0.0,
            'local_unofficial_inr'   => 0.0,
            'import_outstanding_inr' => 0.0,
            'import_outstanding_usd' => 0.0,
            'import_outstanding_rmb' => 0.0,
            'import_official_inr'    => 0.0,
            'import_official_usd'    => 0.0,
            'import_official_rmb'    => 0.0,
            'import_unofficial_inr'  => 0.0,
            'import_unofficial_usd'  => 0.0,
            'import_unofficial_rmb'  => 0.0,
            'supplier_count'         => 0,
            'local_count'            => 0,
            'import_count'           => 0,
        ];

        foreach ($suppliers as $s) {
            $summary['supplier_count']++;
            $sid   = (int)$s['id'];
            $stype = $s['type'];

            $seed_inr = (float)($s['outstanding_inr'] ?? 0);
            $seed_usd = (float)($s['outstanding_usd'] ?? 0);
            $seed_rmb = (float)($s['outstanding_rmb'] ?? 0);

            // Official starts at 0; Unofficial inherits seed opening balance (per ledger convention)
            $off_inr   = 0.0; 
            $off_usd   = 0.0; 
            $off_rmb   = 0.0;
            $unoff_inr = $seed_inr; 
            $unoff_usd = $seed_usd; 
            $unoff_rmb = $seed_rmb;

            if ($stype === 'local') {
                $summary['local_count']++;
                $po = $this->db->query("SELECT 
                    SUM(net_sales_value_1) as off_inr,
                    SUM(total_black_amount_summary) as unoff_inr
                    FROM purchase_order 
                    WHERE supplier_id = {$sid} AND method = 'local' AND delivery_status = 'purchase_in' AND is_deleted = 0")->row_array();

                $off_inr   += (float)($po['off_inr'] ?? 0);
                $unoff_inr += (float)($po['unoff_inr'] ?? 0);
            } else {
                $summary['import_count']++;
                $po = $this->db->query("SELECT 
                    SUM(pp.actual_qty * pp.unit_price_rmb) as tot_rmb,
                    SUM(pp.actual_qty * pp.actual_usd) as tot_usd,
                    SUM(pp.actual_qty * pp.actual_inr) as tot_inr,
                    SUM(pp.total_amount_usd) as off_usd,
                    SUM(pp.official_total_rs) as off_inr,
                    SUM(pp.official_ci_qty * pp.unit_price_rmb) as off_rmb
                    FROM purchase_order po
                    JOIN purchase_in_product pp ON po.id = pp.parent_id
                    WHERE pp.supplier_id = {$sid} AND po.delivery_status = 'purchase_in' AND po.is_deleted = 0")->row_array();

                $p_tot_rmb = (float)($po['tot_rmb'] ?? 0);
                $p_tot_usd = (float)($po['tot_usd'] ?? 0);
                $p_tot_inr = (float)($po['tot_inr'] ?? 0);
                
                $p_off_rmb = (float)($po['off_rmb'] ?? 0);
                $p_off_usd = (float)($po['off_usd'] ?? 0);
                $p_off_inr = (float)($po['off_inr'] ?? 0);

                $p_unoff_rmb = max(0, $p_tot_rmb - $p_off_rmb);
                $p_unoff_usd = max(0, $p_tot_usd - $p_off_usd);
                $p_unoff_inr = max(0, $p_tot_inr - $p_off_inr);

                $off_inr += $p_off_inr;
                $off_usd += $p_off_usd;
                $off_rmb += $p_off_rmb;

                $unoff_inr += $p_unoff_inr;
                $unoff_usd += $p_unoff_usd;
                $unoff_rmb += $p_unoff_rmb;
            }

            // Payments (type = 'payment', is_delete = 0)
            $pays = $this->db->query("SELECT payment_type, payment_method, SUM(amount_rs) as inr, SUM(amount_dollar) as usd, SUM(amount_rmb) as rmb FROM payments WHERE supplier_id = {$sid} AND is_delete = 0 AND type = 'payment' GROUP BY payment_type, payment_method")->result_array();
            foreach ($pays as $p) {
                $is_off = ($p['payment_type'] === 'official' || strtolower($p['payment_method']) === 'cheque');
                $p_inr  = (float)($p['inr'] ?? 0);
                $p_usd  = (float)($p['usd'] ?? 0);
                $p_rmb  = (float)($p['rmb'] ?? 0);
                if ($is_off) {
                    $off_inr -= $p_inr;
                    $off_usd -= $p_usd;
                    $off_rmb -= $p_rmb;
                } else {
                    $unoff_inr -= $p_inr;
                    $unoff_usd -= $p_usd;
                    $unoff_rmb -= $p_rmb;
                }
            }

            // Adjustments
            $adjs = $this->db->query("SELECT type, amt_type, SUM(inr) as inr, SUM(usd) as usd, SUM(rmb) as rmb FROM supplier_adjustments WHERE supplier_id = {$sid} AND is_deleted = 0 GROUP BY type, amt_type")->result_array();
            foreach ($adjs as $a) {
                $is_off = ($a['type'] === 'official');
                $sign   = ($a['amt_type'] === 'plus') ? 1 : -1;
                $a_inr  = $sign * (float)($a['inr'] ?? 0);
                $a_usd  = $sign * (float)($a['usd'] ?? 0);
                $a_rmb  = $sign * (float)($a['rmb'] ?? 0);
                if ($is_off) {
                    $off_inr += $a_inr;
                    $off_usd += $a_usd;
                    $off_rmb += $a_rmb;
                } else {
                    $unoff_inr += $a_inr;
                    $unoff_usd += $a_usd;
                    $unoff_rmb += $a_rmb;
                }
            }

            $summary['official_outstanding']   += $off_inr;
            $summary['official_usd']           += $off_usd;
            $summary['official_rmb']           += $off_rmb;
            $summary['unofficial_outstanding'] += $unoff_inr;
            $summary['unofficial_usd']         += $unoff_usd;
            $summary['unofficial_rmb']         += $unoff_rmb;

            if ($stype === 'local') {
                $summary['local_official_inr']    += $off_inr;
                $summary['local_unofficial_inr']  += $unoff_inr;
                $summary['local_outstanding_inr'] += ($off_inr + $unoff_inr);
            } else {
                $summary['import_official_inr']    += $off_inr;
                $summary['import_official_usd']    += $off_usd;
                $summary['import_official_rmb']    += $off_rmb;
                $summary['import_unofficial_inr']  += $unoff_inr;
                $summary['import_unofficial_usd']  += $unoff_usd;
                $summary['import_unofficial_rmb']  += $unoff_rmb;
                $summary['import_outstanding_inr'] += ($off_inr + $unoff_inr);
                $summary['import_outstanding_usd'] += ($off_usd + $unoff_usd);
                $summary['import_outstanding_rmb'] += ($off_rmb + $unoff_rmb);
            }
        }

        $summary['total_outstanding_inr'] = $summary['official_outstanding'] + $summary['unofficial_outstanding'];
        $summary['total_outstanding_usd'] = $summary['official_usd'] + $summary['unofficial_usd'];
        $summary['total_outstanding_rmb'] = $summary['official_rmb'] + $summary['unofficial_rmb'];

        // Legacy / compatibility keys
        $summary['outstanding_usd'] = $summary['total_outstanding_usd'];
        $summary['outstanding_rmb'] = $summary['total_outstanding_rmb'];

        return $summary;
    }

    /**
     * Get Real-time Total Vendor Outstandings based on Ledger Logic
     * (Opening + PO Expenses - Vendor Payments + Adjustments, Official vs Unofficial, All Currencies)
     */
    public function get_vendor_outstanding_summary($company_id)
    {
        $company_id = (int)$company_id;
        $comp_where = !empty($company_id) ? " AND v.company_id = '{$company_id}'" : "";

        $vendors = $this->db->query("SELECT id, name, outstanding, is_usd, is_rmb, is_inr FROM my_companies v WHERE v.is_deleted = 0 {$comp_where}")->result_array();

        $summary = [
            'total_outstanding_inr'  => 0.0,
            'total_outstanding_usd'  => 0.0,
            'total_outstanding_rmb'  => 0.0,
            'official_outstanding'   => 0.0,
            'official_usd'           => 0.0,
            'official_rmb'           => 0.0,
            'unofficial_outstanding' => 0.0,
            'unofficial_usd'         => 0.0,
            'unofficial_rmb'         => 0.0,
            'vendor_count'           => 0,
        ];

        foreach ($vendors as $v) {
            $summary['vendor_count']++;
            $vid = (int)$v['id'];

            $seed_inr = (float)($v['outstanding'] ?? 0);
            $seed_usd = 0.0;
            $seed_rmb = 0.0;

            $off_inr   = 0.0; 
            $off_usd   = 0.0; 
            $off_rmb   = 0.0;
            $unoff_inr = $seed_inr; 
            $unoff_usd = $seed_usd; 
            $unoff_rmb = $seed_rmb;

            // Expenses (po_expense)
            $exps = $this->db->query("SELECT type, SUM(grand_total) as inr, SUM(usd) as usd, SUM(rmb) as rmb FROM po_expense WHERE vendor_id = {$vid} AND is_delete = 0 GROUP BY type")->result_array();
            foreach ($exps as $e) {
                $is_off = ($e['type'] === 'official');
                $e_inr  = (float)($e['inr'] ?? 0);
                $e_usd  = (float)($e['usd'] ?? 0);
                $e_rmb  = (float)($e['rmb'] ?? 0);
                if ($is_off) {
                    $off_inr += $e_inr;
                    $off_usd += $e_usd;
                    $off_rmb += $e_rmb;
                } else {
                    $unoff_inr += $e_inr;
                    $unoff_usd += $e_usd;
                    $unoff_rmb += $e_rmb;
                }
            }

            // Payments (vendor_payments)
            $pays = $this->db->query("SELECT payment_type, payment_method, SUM(inr) as inr, SUM(usd) as usd, SUM(rmb) as rmb FROM vendor_payments WHERE vendor_id = {$vid} AND is_delete = 0 GROUP BY payment_type, payment_method")->result_array();
            foreach ($pays as $p) {
                $is_off = ($p['payment_type'] === 'official' || strtolower($p['payment_method']) === 'cheque');
                $p_inr  = (float)($p['inr'] ?? 0);
                $p_usd  = (float)($p['usd'] ?? 0);
                $p_rmb  = (float)($p['rmb'] ?? 0);
                if ($is_off) {
                    $off_inr -= $p_inr;
                    $off_usd -= $p_usd;
                    $off_rmb -= $p_rmb;
                } else {
                    $unoff_inr -= $p_inr;
                    $unoff_usd -= $p_usd;
                    $unoff_rmb -= $p_rmb;
                }
            }

            // Adjustments (vendor_adjustments)
            $adjs = $this->db->query("SELECT type, amt_type, SUM(inr) as inr, SUM(usd) as usd, SUM(rmb) as rmb FROM vendor_adjustments WHERE vendor_id = {$vid} AND is_deleted = 0 GROUP BY type, amt_type")->result_array();
            foreach ($adjs as $a) {
                $is_off = ($a['type'] === 'official');
                $sign   = ($a['amt_type'] === 'plus') ? 1 : -1;
                $a_inr  = $sign * (float)($a['inr'] ?? 0);
                $a_usd  = $sign * (float)($a['usd'] ?? 0);
                $a_rmb  = $sign * (float)($a['rmb'] ?? 0);
                if ($is_off) {
                    $off_inr += $a_inr;
                    $off_usd += $a_usd;
                    $off_rmb += $a_rmb;
                } else {
                    $unoff_inr += $a_inr;
                    $unoff_usd += $a_usd;
                    $unoff_rmb += $a_rmb;
                }
            }

            $summary['official_outstanding']   += $off_inr;
            $summary['official_usd']           += $off_usd;
            $summary['official_rmb']           += $off_rmb;
            $summary['unofficial_outstanding'] += $unoff_inr;
            $summary['unofficial_usd']         += $unoff_usd;
            $summary['unofficial_rmb']         += $unoff_rmb;
        }

        $summary['total_outstanding_inr'] = $summary['official_outstanding'] + $summary['unofficial_outstanding'];
        $summary['total_outstanding_usd'] = $summary['official_usd'] + $summary['unofficial_usd'];
        $summary['total_outstanding_rmb'] = $summary['official_rmb'] + $summary['unofficial_rmb'];

        return $summary;
    }

    /**
     * Get Company-Wise Consolidated Procurement Matrix
     */
    public function get_company_wise_purchase_matrix($from_date, $to_date)
    {
        $companies = $this->db->query("SELECT id, name, gst_no FROM company WHERE is_deleted = 0 ORDER BY id ASC")->result_array();

        $rows = [];
        $grand = [
            'local_white'     => 0.0,
            'local_black'     => 0.0,
            'local_total'     => 0.0,
            'import_white'    => 0.0,
            'import_black'    => 0.0,
            'import_total'    => 0.0,
            'combined_white'  => 0.0,
            'combined_black'  => 0.0,
            'combined_total'  => 0.0,
            'expense_white'   => 0.0,
            'expense_black'   => 0.0,
            'expense_total'   => 0.0,
            'payment_white'   => 0.0,
            'payment_black'   => 0.0,
            'payment_total'   => 0.0,
            'outstanding'     => 0.0,
        ];

        foreach ($companies as $comp) {
            $cid = (int)$comp['id'];

            $loc  = $this->get_local_purchase_summary($cid, $from_date, $to_date);
            $imp  = $this->get_import_purchase_summary($cid, $from_date, $to_date);
            $exp  = $this->get_po_expense_summary($cid, $from_date, $to_date);
            $pay  = $this->get_supplier_payments_summary($cid, $from_date, $to_date);
            $out  = $this->get_supplier_outstanding_summary($cid);

            $loc_w = (float)$loc['white_purchase_amount'];
            $loc_b = (float)$loc['black_purchase_amount'];
            $loc_t = (float)$loc['gross_purchase_amount'];

            $imp_w = (float)$imp['white_purchase_amount'];
            $imp_b = (float)$imp['black_purchase_amount'];
            $imp_t = (float)$imp['gross_purchase_amount'];

            $comb_w = $loc_w + $imp_w;
            $comb_b = $loc_b + $imp_b;
            $comb_t = $loc_t + $imp_t;

            $exp_w = (float)$exp['white_expense_amount'];
            $exp_b = (float)$exp['black_expense_amount'];
            $exp_t = (float)$exp['gross_expense_amount'];

            $pay_w = (float)$pay['white_payment_amount'];
            $pay_b = (float)$pay['black_payment_amount'];
            $pay_t = (float)$pay['gross_payment_amount'];

            $out_t = (float)$out['total_outstanding_inr'];

            $rows[] = [
                'company_id'      => $cid,
                'company_name'    => $comp['name'],
                'gst_no'          => $comp['gst_no'] ?? '',
                'local_white'     => $loc_w,
                'local_black'     => $loc_b,
                'local_total'     => $loc_t,
                'import_white'    => $imp_w,
                'import_black'    => $imp_b,
                'import_total'    => $imp_t,
                'combined_white'  => $comb_w,
                'combined_black'  => $comb_b,
                'combined_total'  => $comb_t,
                'expense_white'   => $exp_w,
                'expense_black'   => $exp_b,
                'expense_total'   => $exp_t,
                'payment_white'   => $pay_w,
                'payment_black'   => $pay_b,
                'payment_total'   => $pay_t,
                'outstanding'     => $out_t,
            ];

            $grand['local_white']    += $loc_w;
            $grand['local_black']    += $loc_b;
            $grand['local_total']    += $loc_t;
            $grand['import_white']   += $imp_w;
            $grand['import_black']   += $imp_b;
            $grand['import_total']   += $imp_t;
            $grand['combined_white'] += $comb_w;
            $grand['combined_black'] += $comb_b;
            $grand['combined_total'] += $comb_t;
            $grand['expense_white']  += $exp_w;
            $grand['expense_black']  += $exp_b;
            $grand['expense_total']  += $exp_t;
            $grand['payment_white']  += $pay_w;
            $grand['payment_black']  += $pay_b;
            $grand['payment_total']  += $pay_t;
            $grand['outstanding']    += $out_t;
        }

        return [
            'rows'   => $rows,
            'totals' => $grand,
        ];
    }

    /**
     * Get Procurement Channel Breakdown Matrix for the Active Company
     */
    public function get_active_company_channel_matrix($company_id, $from_date, $to_date)
    {
        $loc      = $this->get_local_purchase_summary($company_id, $from_date, $to_date);
        $imp      = $this->get_import_purchase_summary($company_id, $from_date, $to_date);
        $exp      = $this->get_po_expense_summary($company_id, $from_date, $to_date);
        $supp_pay = $this->get_supplier_payments_summary($company_id, $from_date, $to_date);
        $vend_pay = $this->get_vendor_payments_summary($company_id, $from_date, $to_date);
        $supp_out = $this->get_supplier_outstanding_summary($company_id);
        $vend_out = $this->get_vendor_outstanding_summary($company_id);

        $rows = [
            [
                'channel'      => 'Local Purchases',
                'type_tag'     => 'tag-local',
                'orders_count' => (int)$loc['total_orders'],
                'basic_value'  => (float)$loc['total_basic_value'],
                'tax_amount'   => (float)$loc['total_gst'],
                'white_amount' => (float)$loc['white_purchase_amount'],
                'black_amount' => (float)$loc['black_purchase_amount'],
                'gross_total'  => (float)$loc['gross_purchase_amount'],
                'paid_amount'  => (float)$supp_pay['local_payments']['total'],
                'outstanding'  => (float)$supp_out['local_outstanding_inr'],
            ],
            [
                'channel'      => 'Import Purchases',
                'type_tag'     => 'tag-import',
                'orders_count' => (int)$imp['total_orders'],
                'basic_value'  => max(0, (float)$imp['white_purchase_amount'] - (float)$imp['customs_tax_amount']),
                'tax_amount'   => (float)$imp['customs_tax_amount'],
                'white_amount' => (float)$imp['white_purchase_amount'],
                'black_amount' => (float)$imp['black_purchase_amount'],
                'gross_total'  => (float)$imp['gross_purchase_amount'],
                'paid_amount'  => (float)$supp_pay['import_payments']['total'],
                'outstanding'  => (float)$supp_out['import_outstanding_inr'],
            ],
            [
                'channel'      => 'PO Operational Expenses',
                'type_tag'     => 'tag-unofficial',
                'orders_count' => (int)$exp['total_count'],
                'basic_value'  => (float)$exp['white_sub_total'],
                'tax_amount'   => (float)$exp['white_gst_total'],
                'white_amount' => (float)$exp['white_expense_amount'],
                'black_amount' => (float)$exp['black_expense_amount'],
                'gross_total'  => (float)$exp['gross_expense_amount'],
                'paid_amount'  => (float)$vend_pay['gross_payment_amount'],
                'outstanding'  => (float)$vend_out['total_outstanding_inr'],
            ],
        ];

        $totals = [
            'orders_count' => $rows[0]['orders_count'] + $rows[1]['orders_count'] + $rows[2]['orders_count'],
            'basic_value'  => $rows[0]['basic_value'] + $rows[1]['basic_value'] + $rows[2]['basic_value'],
            'tax_amount'   => $rows[0]['tax_amount'] + $rows[1]['tax_amount'] + $rows[2]['tax_amount'],
            'white_amount' => $rows[0]['white_amount'] + $rows[1]['white_amount'] + $rows[2]['white_amount'],
            'black_amount' => $rows[0]['black_amount'] + $rows[1]['black_amount'] + $rows[2]['black_amount'],
            'gross_total'  => $rows[0]['gross_total'] + $rows[1]['gross_total'] + $rows[2]['gross_total'],
            'paid_amount'  => (float)$supp_pay['gross_payment_amount'] + (float)$vend_pay['gross_payment_amount'],
            'outstanding'  => (float)$supp_out['total_outstanding_inr'] + (float)$vend_out['total_outstanding_inr'],
        ];

        return [
            'rows'   => $rows,
            'totals' => $totals,
        ];
    }

    /**
     * Get Daily Purchase Trend for ApexCharts
     */
    public function get_daily_trend($company_id, $from_date, $to_date)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND po.company_id = '{$company_id}'" : "";

        $curr = strtotime($from_date);
        $last = strtotime($to_date);
        $diff_days = ($last - $curr) / 86400;
        $is_monthly = ($diff_days > 45);

        $date_fmt = $is_monthly ? "%Y-%m" : "%Y-%m-%d";

        $sql = "SELECT 
                    DATE_FORMAT(po.date, '{$date_fmt}') as tx_period,
                    IFNULL(SUM(po.grand_total), 0) as white_purchase,
                    IFNULL(SUM(po.total_black_amount_summary), 0) as black_purchase,
                    IFNULL(SUM(po.grand_total + po.total_black_amount_summary), 0) as total_purchase
                FROM purchase_order po
                WHERE po.is_deleted = '0'
                  {$comp_filter}
                  AND (DATE(po.date) BETWEEN '{$from_date}' AND '{$to_date}')
                GROUP BY DATE_FORMAT(po.date, '{$date_fmt}')
                ORDER BY tx_period ASC";

        $rows = $this->db->query($sql)->result_array();
        $map = [];

        if ($is_monthly) {
            $iter = strtotime(date('Y-m-01', $curr));
            $end_iter = strtotime(date('Y-m-01', $last));
            while ($iter <= $end_iter) {
                $p_str = date('Y-m', $iter);
                $map[$p_str] = [
                    'label' => date('M Y', $iter),
                    'white' => 0.0,
                    'black' => 0.0,
                    'total' => 0.0,
                ];
                $iter = strtotime('+1 month', $iter);
            }
        } else {
            $iter = $curr;
            while ($iter <= $last) {
                $p_str = date('Y-m-d', $iter);
                $map[$p_str] = [
                    'label' => date('d M', $iter),
                    'white' => 0.0,
                    'black' => 0.0,
                    'total' => 0.0,
                ];
                $iter = strtotime('+1 day', $iter);
            }
        }

        foreach ($rows as $r) {
            $key = $r['tx_period'];
            if (isset($map[$key])) {
                $map[$key]['white'] = (float)$r['white_purchase'];
                $map[$key]['black'] = (float)$r['black_purchase'];
                $map[$key]['total'] = (float)$r['total_purchase'];
            }
        }

        $categories = [];
        $s_white    = [];
        $s_black    = [];
        $s_total    = [];

        foreach ($map as $entry) {
            $categories[] = $entry['label'];
            $s_white[]    = round($entry['white'], 2);
            $s_black[]    = round($entry['black'], 2);
            $s_total[]    = round($entry['total'], 2);
        }

        return [
            'is_monthly' => $is_monthly,
            'categories' => $categories,
            'series'     => [
                [
                    'name' => 'White Purchase (Official)',
                    'data' => $s_white,
                ],
                [
                    'name' => 'Black Purchase (Unofficial)',
                    'data' => $s_black,
                ],
                [
                    'name' => 'Total Purchase',
                    'data' => $s_total,
                ],
            ],
        ];
    }

    /**
     * Get Top Suppliers by Purchase Volume
     */
    /**
     * Get Top Suppliers by Purchase Volume
     * Accurately aggregates per supplier at the product level (purchase_order_product and purchase_in_product)
     * so that purchase orders containing multiple suppliers or CSV supplier lists are properly apportioned.
     */
    public function get_top_suppliers($company_id, $from_date, $to_date, $limit = 5)
    {
        $company_id = (int)$company_id;
        $comp_filter_po = !empty($company_id) ? " AND po.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        // 1. Local Purchase Order Products
        $local_sql = "
            SELECT 
                COALESCE(NULLIF(pop.supplier_id, 0), NULLIF(po.supplier_id, 0)) as supplier_id,
                'local' as method,
                po.id as po_id,
                IFNULL(pop.grand_total, 0) as white_amt,
                IFNULL(pop.black_amt_total, 0) as black_amt
            FROM purchase_order po
            JOIN purchase_order_product pop ON po.id = pop.parent_id
            WHERE po.is_deleted = '0'
              AND po.method IN ('local', 'company')
              {$comp_filter_po}
              AND (DATE(po.date) BETWEEN '{$from_date}' AND '{$to_date}')
        ";

        // 2. Import Invoiced Shipments (purchase_in_product)
        $import_pip_sql = "
            SELECT 
                pip.supplier_id,
                'import' as method,
                po.id as po_id,
                IFNULL(pip.official_total_rs, 0) as white_amt,
                IFNULL(CASE 
                    WHEN pip.black_total_price > 0 THEN pip.black_total_price 
                    WHEN (pip.actual_qty * pip.actual_inr) > pip.official_total_rs THEN ((pip.actual_qty * pip.actual_inr) - pip.official_total_rs)
                    ELSE 0 
                END, 0) as black_amt
            FROM purchase_order po
            JOIN purchase_in_product pip ON po.id = pip.parent_id
            WHERE po.is_deleted = '0'
              AND po.method = 'import'
              {$comp_filter_po}
              AND (DATE(po.date) BETWEEN '{$from_date}' AND '{$to_date}')
        ";

        // 3. Pending/Transit Import Orders (not yet in purchase_in_product)
        $import_pop_sql = "
            SELECT 
                pop.supplier_id,
                'import' as method,
                po.id as po_id,
                IFNULL(pop.total_val, 0) as white_amt,
                IFNULL(pop.black_amt_total, 0) as black_amt
            FROM purchase_order po
            JOIN purchase_order_product pop ON po.id = pop.parent_id
            WHERE po.is_deleted = '0'
              AND po.method = 'import'
              {$comp_filter_po}
              AND (DATE(po.date) BETWEEN '{$from_date}' AND '{$to_date}')
              AND po.id NOT IN (SELECT DISTINCT parent_id FROM purchase_in_product)
        ";

        $sql = "SELECT 
                    t.supplier_id,
                    COALESCE(s.name, 'Unknown Supplier') as supplier_name,
                    COALESCE(s.type, t.method) as method,
                    COUNT(DISTINCT t.po_id) as order_count,
                    IFNULL(SUM(t.white_amt), 0) as white_amt,
                    IFNULL(SUM(t.black_amt), 0) as black_amt,
                    IFNULL(SUM(t.white_amt + t.black_amt), 0) as total_amt
                FROM (
                    {$local_sql}
                    UNION ALL
                    {$import_pip_sql}
                    UNION ALL
                    {$import_pop_sql}
                ) t
                LEFT JOIN supplier s ON s.id = t.supplier_id
                WHERE t.supplier_id IS NOT NULL AND t.supplier_id > 0
                GROUP BY t.supplier_id, s.name, s.type, t.method
                ORDER BY total_amt DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->result_array();
    }

    /**
     * Get Top Outstanding Suppliers (Payables)
     */
    public function get_top_outstanding_suppliers($company_id, $limit = 5)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND s.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        $sql = "SELECT 
                    s.id,
                    s.name,
                    s.type,
                    s.contact_no,
                    s.outstanding_inr,
                    s.outstanding_usd,
                    s.outstanding_rmb
                FROM supplier s
                WHERE s.is_deleted = 0
                  AND (s.outstanding_inr > 0 OR s.outstanding_usd > 0 OR s.outstanding_rmb > 0)
                  {$comp_filter}
                ORDER BY s.outstanding_inr DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->result_array();
    }

    /**
     * Get Recent Purchase Orders
     */
    public function get_recent_purchase_orders($company_id, $limit = 8)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND po.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        $sql = "SELECT 
                    po.id,
                    po.voucher_no,
                    po.date,
                    po.method,
                    po.supplier_id,
                    COALESCE(
                        NULLIF(po.supplier_name, ''),
                        (
                            SELECT GROUP_CONCAT(DISTINCT s.name SEPARATOR ', ')
                            FROM (
                                SELECT parent_id, supplier_id FROM purchase_in_product WHERE supplier_id > 0
                                UNION
                                SELECT parent_id, supplier_id FROM purchase_order_product WHERE supplier_id > 0
                            ) sp
                            JOIN supplier s ON s.id = sp.supplier_id
                            WHERE sp.parent_id = po.id
                        ),
                        '—'
                    ) as supplier_name,
                    po.company_name,
                    po.grand_total as white_amt,
                    po.total_black_amount_summary as black_amt,
                    (po.grand_total + po.total_black_amount_summary) as total_amt,
                    po.delivery_status
                FROM purchase_order po
                WHERE po.is_deleted = '0'
                  {$comp_filter}
                ORDER BY po.date DESC, po.id DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->result_array();
    }

    /**
     * Get Recent Supplier Payments
     */
    public function get_recent_supplier_payments($company_id, $limit = 8)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND p.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        $sql = "SELECT 
                    p.id,
                    p.payment_date as date,
                    p.invoice_no,
                    p.supplier_id,
                    p.supplier_name,
                    p.amount_rs as amount,
                    p.payment_type,
                    p.payment_method
                FROM payments p
                WHERE p.is_delete = 0
                  AND p.type = 'payment'
                  {$comp_filter}
                ORDER BY p.payment_date DESC, p.id DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->result_array();
    }
}
