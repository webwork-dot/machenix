<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_dashboard_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get Sales Performance Summary (Amount and Invoices)
     */
    public function get_sales_amount_summary($company_id, $from_date, $to_date)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND so.company_id = '{$company_id}'" : "";

        $sql = "SELECT 
                    COUNT(DISTINCT so.id) as total_orders,
                    IFNULL(SUM(so.basic_value), 0) as total_basic_value,
                    IFNULL(SUM(so.gst_total), 0) as total_gst,
                    IFNULL(SUM(so.net_sales_value_1), 0) as white_sales_amount,
                    IFNULL(SUM(so.total_black_amt), 0) as black_sales_amount,
                    IFNULL(SUM(so.other_charges_amount), 0) as other_charges,
                    IFNULL(SUM(so.round_of), 0) as round_of,
                    IFNULL(SUM(so.grand_total), 0) as gross_sales_amount
                FROM sales_order so
                WHERE so.is_deleted = '0'
                  AND (so.is_cancelled = 0 OR so.is_cancelled IS NULL)
                  {$comp_filter}
                  AND (DATE(so.date) BETWEEN '{$from_date}' AND '{$to_date}')";

        $res = $this->db->query($sql)->row_array();

        // Get Sales Returns for the same period
        $ret_comp_filter = !empty($company_id) ? " AND company_id = '{$company_id}'" : "";
        $ret_sql = "SELECT 
                        COUNT(id) as total_returns,
                        IFNULL(SUM(white_total + gst_total_amt), 0) as white_return_amount,
                        IFNULL(SUM(black_total), 0) as black_return_amount,
                        IFNULL(SUM(grand_total), 0) as total_return_amount
                    FROM goods_return
                    WHERE is_deleted = 0
                      {$ret_comp_filter}
                      AND (DATE(date) BETWEEN '{$from_date}' AND '{$to_date}')";

        $ret_res = $this->db->query($ret_sql)->row_array();

        $gross_white = (float)($res['white_sales_amount'] ?? 0);
        $gross_black = (float)($res['black_sales_amount'] ?? 0);
        $gross_total = (float)($res['gross_sales_amount'] ?? 0);

        $ret_white   = (float)($ret_res['white_return_amount'] ?? 0);
        $ret_black   = (float)($ret_res['black_return_amount'] ?? 0);
        $ret_total   = (float)($ret_res['total_return_amount'] ?? 0);

        return [
            'total_orders'        => (int)($res['total_orders'] ?? 0),
            'total_basic_value'   => (float)($res['total_basic_value'] ?? 0),
            'total_gst'           => (float)($res['total_gst'] ?? 0),
            'white_sales_amount'  => $gross_white,
            'black_sales_amount'  => $gross_black,
            'gross_sales_amount'  => $gross_total,
            'white_return_amount' => $ret_white,
            'black_return_amount' => $ret_black,
            'total_return_amount' => $ret_total,
            'net_white_sales'     => $gross_white - $ret_white,
            'net_black_sales'     => $gross_black - $ret_black,
            'net_sales_amount'    => $gross_total - $ret_total,
            'total_returns'       => (int)($ret_res['total_returns'] ?? 0),
        ];
    }

    /**
     * Get Sales Stock (Quantity) Summary
     */
    public function get_sales_stock_summary($company_id, $from_date, $to_date)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND so.company_id = '{$company_id}'" : "";

        // Batch-level quantity breakdown
        $sql = "SELECT 
                    IFNULL(SUM(sob.white_qty), 0) as white_stock_qty,
                    IFNULL(SUM(sob.black_qty), 0) as black_stock_qty,
                    IFNULL(SUM(sob.white_qty + sob.black_qty), 0) as total_stock_qty
                FROM sales_order_product_batch sob
                INNER JOIN sales_order so ON so.id = sob.order_id
                WHERE so.is_deleted = '0'
                  AND (so.is_cancelled = 0 OR so.is_cancelled IS NULL)
                  {$comp_filter}
                  AND (DATE(so.date) BETWEEN '{$from_date}' AND '{$to_date}')";

        $res = $this->db->query($sql)->row_array();

        // If batch entries are zero, fallback to sales_order_product total qty
        $white_qty = (float)($res['white_stock_qty'] ?? 0);
        $black_qty = (float)($res['black_stock_qty'] ?? 0);
        $total_qty = (float)($res['total_stock_qty'] ?? 0);

        if ($total_qty == 0) {
            $fallback_sql = "SELECT IFNULL(SUM(sop.qty), 0) as total_sop_qty
                             FROM sales_order_product sop
                             INNER JOIN sales_order so ON so.id = sop.order_id
                             WHERE so.is_deleted = '0'
                               AND (so.is_cancelled = 0 OR so.is_cancelled IS NULL)
                               {$comp_filter}
                               AND (DATE(so.date) BETWEEN '{$from_date}' AND '{$to_date}')";
            $sop_res = $this->db->query($fallback_sql)->row_array();
            $total_qty = (float)($sop_res['total_sop_qty'] ?? 0);
            $white_qty = $total_qty; // default to white if unbatched
        }

        // Goods return quantity
        $ret_comp_filter = !empty($company_id) ? " AND gr.company_id = '{$company_id}'" : "";
        $ret_sql = "SELECT 
                        IFNULL(SUM(grp.white_qty), 0) as white_return_qty,
                        IFNULL(SUM(grp.black_qty), 0) as black_return_qty,
                        IFNULL(SUM(CASE WHEN (grp.white_qty + grp.black_qty) > 0 THEN (grp.white_qty + grp.black_qty) ELSE grp.quantity END), 0) as total_return_qty
                    FROM goods_return_product grp
                    INNER JOIN goods_return gr ON gr.id = grp.parent_id
                    WHERE gr.is_deleted = 0
                      {$ret_comp_filter}
                      AND (DATE(gr.date) BETWEEN '{$from_date}' AND '{$to_date}')";

        $ret_res = $this->db->query($ret_sql)->row_array();
        $ret_white = (float)($ret_res['white_return_qty'] ?? 0);
        $ret_black = (float)($ret_res['black_return_qty'] ?? 0);
        $ret_total = (float)($ret_res['total_return_qty'] ?? 0);

        return [
            'white_stock_qty'   => $white_qty,
            'black_stock_qty'   => $black_qty,
            'total_stock_qty'   => $total_qty,
            'white_return_qty'  => $ret_white,
            'black_return_qty'  => $ret_black,
            'total_return_qty'  => $ret_total,
            'net_white_stock'   => $white_qty - $ret_white,
            'net_black_stock'   => $black_qty - $ret_black,
            'net_total_stock'   => $total_qty - $ret_total,
        ];
    }

    /**
     * Get Payment Collection Summary (4-Way Matrix: White/Black x Cash/Bank)
     */
    public function get_payment_collection_summary($company_id, $from_date, $to_date)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND company_id = '{$company_id}'" : "";

        $sql = "SELECT 
                    payment_type,
                    LOWER(payment_method) as method,
                    payment_mode,
                    IFNULL(SUM(amount), 0) as amt,
                    COUNT(id) as count
                FROM customer_payment
                WHERE is_deleted = 0
                  {$comp_filter}
                  AND (DATE(date) BETWEEN '{$from_date}' AND '{$to_date}')
                GROUP BY payment_type, LOWER(payment_method), payment_mode";

        $q = $this->db->query($sql);

        $matrix = [
            'official' => [
                'cash'   => ['in' => 0.0, 'return' => 0.0, 'net' => 0.0, 'count' => 0],
                'cheque' => ['in' => 0.0, 'return' => 0.0, 'net' => 0.0, 'count' => 0], // cheque = bank
                'total'  => ['in' => 0.0, 'return' => 0.0, 'net' => 0.0, 'count' => 0],
            ],
            'unofficial' => [
                'cash'   => ['in' => 0.0, 'return' => 0.0, 'net' => 0.0, 'count' => 0],
                'cheque' => ['in' => 0.0, 'return' => 0.0, 'net' => 0.0, 'count' => 0],
                'total'  => ['in' => 0.0, 'return' => 0.0, 'net' => 0.0, 'count' => 0],
            ],
            'totals' => [
                'total_cash_in'     => 0.0,
                'total_cash_return' => 0.0,
                'total_cash_net'    => 0.0,
                'total_bank_in'     => 0.0,
                'total_bank_return' => 0.0,
                'total_bank_net'    => 0.0,
                'gross_collection'  => 0.0,
                'total_returns'     => 0.0,
                'net_collection'    => 0.0,
                'total_tx_count'    => 0,
            ]
        ];

        if (!empty($q)) {
            foreach ($q->result_array() as $row) {
                $ptype  = ($row['payment_type'] === 'unofficial') ? 'unofficial' : 'official';
                $method = (trim($row['method']) === 'cash') ? 'cash' : 'cheque';
                $mode   = ($row['payment_mode'] === 'return') ? 'return' : 'in';
                $amt    = (float)$row['amt'];
                $cnt    = (int)$row['count'];

                $matrix[$ptype][$method][$mode] += $amt;
                $matrix[$ptype][$method]['count'] += $cnt;
            }
        }

        // Calculate nets and totals
        foreach (['official', 'unofficial'] as $ptype) {
            foreach (['cash', 'cheque'] as $m) {
                $matrix[$ptype][$m]['net'] = $matrix[$ptype][$m]['in'] - $matrix[$ptype][$m]['return'];
                $matrix[$ptype]['total']['in'] += $matrix[$ptype][$m]['in'];
                $matrix[$ptype]['total']['return'] += $matrix[$ptype][$m]['return'];
                $matrix[$ptype]['total']['count'] += $matrix[$ptype][$m]['count'];
            }
            $matrix[$ptype]['total']['net'] = $matrix[$ptype]['total']['in'] - $matrix[$ptype]['total']['return'];
        }

        $matrix['totals']['total_cash_in']     = $matrix['official']['cash']['in'] + $matrix['unofficial']['cash']['in'];
        $matrix['totals']['total_cash_return'] = $matrix['official']['cash']['return'] + $matrix['unofficial']['cash']['return'];
        $matrix['totals']['total_cash_net']    = $matrix['totals']['total_cash_in'] - $matrix['totals']['total_cash_return'];

        $matrix['totals']['total_bank_in']     = $matrix['official']['cheque']['in'] + $matrix['unofficial']['cheque']['in'];
        $matrix['totals']['total_bank_return'] = $matrix['official']['cheque']['return'] + $matrix['unofficial']['cheque']['return'];
        $matrix['totals']['total_bank_net']    = $matrix['totals']['total_bank_in'] - $matrix['totals']['total_bank_return'];

        $matrix['totals']['gross_collection']  = $matrix['totals']['total_cash_in'] + $matrix['totals']['total_bank_in'];
        $matrix['totals']['total_returns']     = $matrix['totals']['total_cash_return'] + $matrix['totals']['total_bank_return'];
        $matrix['totals']['net_collection']    = $matrix['totals']['gross_collection'] - $matrix['totals']['total_returns'];
        $matrix['totals']['total_tx_count']    = $matrix['official']['total']['count'] + $matrix['unofficial']['total']['count'];

        return $matrix;
    }

    /**
     * Get Real-time Liquid Funds on Hand (Cash on Hand & Bank Balance)
     * Matches the core accounting ledger audit formulas in Inventory_model.
     */
    public function get_funds_on_hand_summary($company_id)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND company_id = '{$company_id}'" : "";

        // 1. CASH ON HAND
        $cash_summary = [
            'official'   => 0.0,
            'unofficial' => 0.0,
            'total'      => 0.0,
        ];

        // Customer + manual cash receipts / returns
        $sql_cash_cp = "SELECT 
                            payment_type,
                            payment_mode,
                            IFNULL(SUM(amount), 0) as total_amt
                        FROM customer_payment
                        WHERE LOWER(payment_method) = 'cash' 
                          AND is_deleted = 0 
                          {$comp_filter}
                        GROUP BY payment_type, payment_mode";
        $q = $this->db->query($sql_cash_cp);
        if (!empty($q)) {
            foreach ($q->result_array() as $row) {
                $ptype = ($row['payment_type'] == 'unofficial') ? 'unofficial' : 'official';
                $amt   = (float)$row['total_amt'];
                if ($row['payment_mode'] == 'return') {
                    $cash_summary[$ptype] -= $amt;
                } else {
                    $cash_summary[$ptype] += $amt;
                }
            }
        }

        // Supplier cash payments & expenses (-)
        $sql_cash_sp = "SELECT payment_type, IFNULL(SUM(amount_rs), 0) as amt
                        FROM payments
                        WHERE is_delete = 0 AND LOWER(payment_method) = 'cash' {$comp_filter}
                        GROUP BY payment_type";
        $p_q = $this->db->query($sql_cash_sp);
        if (!empty($p_q)) {
            foreach ($p_q->result_array() as $row) {
                $ptype = ($row['payment_type'] == 'unofficial') ? 'unofficial' : 'official';
                $cash_summary[$ptype] -= (float)$row['amt'];
            }
        }

        // Vendor cash payments (-)
        $sql_cash_vp = "SELECT payment_type, IFNULL(SUM(inr), 0) as amt
                        FROM vendor_payments
                        WHERE is_delete = 0 AND LOWER(payment_method) = 'cash' {$comp_filter}
                        GROUP BY payment_type";
        $v_q = $this->db->query($sql_cash_vp);
        if (!empty($v_q)) {
            foreach ($v_q->result_array() as $row) {
                $ptype = ($row['payment_type'] == 'unofficial') ? 'unofficial' : 'official';
                $cash_summary[$ptype] -= (float)$row['amt'];
            }
        }

        // Intercompany cash out (-)
        $t_comp_out = !empty($company_id) ? " AND company_id = '{$company_id}'" : "";
        $sql_t_out = "SELECT converted_from as ptype, IFNULL(SUM(amount), 0) as amt
                      FROM transferred_cash
                      WHERE is_deleted = 0 AND LOWER(method_from) = 'cash'
                        AND NOT (IFNULL(is_reverse,0) = 1 AND IFNULL(is_approved,0) = 0)
                        {$t_comp_out}
                      GROUP BY converted_from";
        $t_q = $this->db->query($sql_t_out);
        if (!empty($t_q)) {
            foreach ($t_q->result_array() as $row) {
                $ptype = ($row['ptype'] == 'unofficial') ? 'unofficial' : 'official';
                $cash_summary[$ptype] -= (float)$row['amt'];
            }
        }

        // Intercompany cash in (+)
        $t_comp_in = !empty($company_id) ? " AND company_to_id = '{$company_id}'" : "";
        $sql_t_in = "SELECT CASE WHEN converted_to IN ('official','unofficial') THEN converted_to ELSE converted_from END as ptype, 
                            IFNULL(SUM(amount), 0) as amt
                     FROM transferred_cash
                     WHERE is_approved = 1 AND LOWER(method_to) = 'cash' AND is_deleted = 0 {$t_comp_in}
                     GROUP BY ptype";
        $in_q = $this->db->query($sql_t_in);
        if (!empty($in_q)) {
            foreach ($in_q->result_array() as $row) {
                $ptype = ($row['ptype'] == 'unofficial') ? 'unofficial' : 'official';
                $cash_summary[$ptype] += (float)$row['amt'];
            }
        }

        // Converted cash out (-)
        $c_comp = !empty($company_id) ? " AND company_id = '{$company_id}'" : "";
        $sql_c_out = "SELECT converted_from as ptype, IFNULL(SUM(amount), 0) as amt
                      FROM converted_payment
                      WHERE LOWER(method_from) = 'cash' AND is_deleted = 0 {$c_comp}
                      GROUP BY converted_from";
        $out_q = $this->db->query($sql_c_out);
        if (!empty($out_q)) {
            foreach ($out_q->result_array() as $row) {
                $ptype = ($row['ptype'] == 'unofficial') ? 'unofficial' : 'official';
                $cash_summary[$ptype] -= (float)$row['amt'];
            }
        }

        // Converted cash in (+)
        $sql_c_in = "SELECT converted_to as ptype, IFNULL(SUM(amount), 0) as amt
                     FROM converted_payment
                     WHERE LOWER(method_to) = 'cash' AND is_deleted = 0 {$c_comp}
                     GROUP BY converted_to";
        $in_q2 = $this->db->query($sql_c_in);
        if (!empty($in_q2)) {
            foreach ($in_q2->result_array() as $row) {
                $ptype = ($row['ptype'] == 'unofficial') ? 'unofficial' : 'official';
                $cash_summary[$ptype] += (float)$row['amt'];
            }
        }

        $cash_summary['total'] = $cash_summary['official'] + $cash_summary['unofficial'];

        // 2. BANK IN HAND (cheque method)
        $bank_summary = [
            'official'   => 0.0,
            'unofficial' => 0.0,
            'total'      => 0.0,
            'accounts'   => []
        ];

        // Customer cheque in (+) / return (-)
        $sql_bank_cp = "SELECT 
                            payment_type,
                            payment_mode,
                            company_bank,
                            IFNULL(SUM(amount), 0) as total_amt
                        FROM customer_payment
                        WHERE LOWER(payment_method) = 'cheque' 
                          AND is_deleted = 0 
                          {$comp_filter}
                        GROUP BY payment_type, payment_mode, company_bank";
        $q_b = $this->db->query($sql_bank_cp);
        if (!empty($q_b)) {
            foreach ($q_b->result_array() as $row) {
                $ptype = ($row['payment_type'] == 'unofficial') ? 'unofficial' : 'official';
                $amt   = (float)$row['total_amt'];
                if ($row['payment_mode'] == 'return') {
                    $bank_summary[$ptype] -= $amt;
                } else {
                    $bank_summary[$ptype] += $amt;
                }
            }
        }

        // Supplier bank payments & expenses (-)
        $sql_bank_sp = "SELECT payment_type, IFNULL(SUM(amount_rs), 0) as amt
                        FROM payments
                        WHERE is_delete = 0 AND LOWER(payment_method) = 'cheque' {$comp_filter}
                        GROUP BY payment_type";
        $p_qb = $this->db->query($sql_bank_sp);
        if (!empty($p_qb)) {
            foreach ($p_qb->result_array() as $row) {
                $ptype = ($row['payment_type'] == 'unofficial') ? 'unofficial' : 'official';
                $bank_summary[$ptype] -= (float)$row['amt'];
            }
        }

        // Vendor bank payments (-)
        $sql_bank_vp = "SELECT payment_type, IFNULL(SUM(inr), 0) as amt
                        FROM vendor_payments
                        WHERE is_delete = 0 AND LOWER(payment_method) = 'cheque' {$comp_filter}
                        GROUP BY payment_type";
        $v_qb = $this->db->query($sql_bank_vp);
        if (!empty($v_qb)) {
            foreach ($v_qb->result_array() as $row) {
                $ptype = ($row['payment_type'] == 'unofficial') ? 'unofficial' : 'official';
                $bank_summary[$ptype] -= (float)$row['amt'];
            }
        }

        // Intercompany bank out (-)
        $sql_tb_out = "SELECT converted_from as ptype, IFNULL(SUM(amount), 0) as amt
                       FROM transferred_cash
                       WHERE is_deleted = 0 AND LOWER(method_from) = 'cheque'
                         AND NOT (IFNULL(is_reverse,0) = 1 AND IFNULL(is_approved,0) = 0)
                         {$t_comp_out}
                       GROUP BY converted_from";
        $t_qb = $this->db->query($sql_tb_out);
        if (!empty($t_qb)) {
            foreach ($t_qb->result_array() as $row) {
                $ptype = ($row['ptype'] == 'unofficial') ? 'unofficial' : 'official';
                $bank_summary[$ptype] -= (float)$row['amt'];
            }
        }

        // Intercompany bank in (+)
        $sql_tb_in = "SELECT CASE WHEN converted_to IN ('official','unofficial') THEN converted_to ELSE converted_from END as ptype, 
                             IFNULL(SUM(amount), 0) as amt
                      FROM transferred_cash
                      WHERE is_approved = 1 AND LOWER(method_to) = 'cheque' AND is_deleted = 0 {$t_comp_in}
                      GROUP BY ptype";
        $in_qb = $this->db->query($sql_tb_in);
        if (!empty($in_qb)) {
            foreach ($in_qb->result_array() as $row) {
                $ptype = ($row['ptype'] == 'unofficial') ? 'unofficial' : 'official';
                $bank_summary[$ptype] += (float)$row['amt'];
            }
        }

        // Converted bank out (-)
        $sql_cb_out = "SELECT converted_from as ptype, IFNULL(SUM(amount), 0) as amt
                       FROM converted_payment
                       WHERE LOWER(method_from) = 'cheque' AND is_deleted = 0 {$c_comp}
                       GROUP BY converted_from";
        $out_qb = $this->db->query($sql_cb_out);
        if (!empty($out_qb)) {
            foreach ($out_qb->result_array() as $row) {
                $ptype = ($row['ptype'] == 'unofficial') ? 'unofficial' : 'official';
                $bank_summary[$ptype] -= (float)$row['amt'];
            }
        }

        // Converted bank in (+)
        $sql_cb_in = "SELECT converted_to as ptype, IFNULL(SUM(amount), 0) as amt
                      FROM converted_payment
                      WHERE LOWER(method_to) = 'cheque' AND is_deleted = 0 {$c_comp}
                      GROUP BY converted_to";
        $in_qb2 = $this->db->query($sql_cb_in);
        if (!empty($in_qb2)) {
            foreach ($in_qb2->result_array() as $row) {
                $ptype = ($row['ptype'] == 'unofficial') ? 'unofficial' : 'official';
                $bank_summary[$ptype] += (float)$row['amt'];
            }
        }

        $bank_summary['total'] = $bank_summary['official'] + $bank_summary['unofficial'];

        // Bank Accounts List
        if (!empty($company_id)) {
            $bank_accs = $this->db->query("SELECT id, bank_name, account_no, name 
                                           FROM bank_accounts 
                                           WHERE is_delete = 0 AND company_id = '{$company_id}' 
                                           ORDER BY id ASC")->result_array();
            $bank_summary['accounts'] = $bank_accs;
        }

        return [
            'cash'  => $cash_summary,
            'bank'  => $bank_summary,
            'total_amount'       => $cash_summary['total'] + $bank_summary['total'],
            'total_liquid_funds' => $cash_summary['total'] + $bank_summary['total']
        ];
    }

    /**
     * Get Daily Sales & Collection Trend for Chart (ApexCharts / Chart.js)
     */
    public function get_daily_trend($company_id, $from_date, $to_date)
    {
        $company_id = (int)$company_id;
        $comp_filter_so = !empty($company_id) ? " AND so.company_id = '{$company_id}'" : "";
        $comp_filter_cp = !empty($company_id) ? " AND cp.company_id = '{$company_id}'" : "";

        // Daily sales breakdown
        $sales_sql = "SELECT 
                          DATE(so.date) as tx_date,
                          IFNULL(SUM(so.net_sales_value_1), 0) as white_sales,
                          IFNULL(SUM(so.total_black_amt), 0) as black_sales,
                          IFNULL(SUM(so.grand_total), 0) as total_sales
                      FROM sales_order so
                      WHERE so.is_deleted = '0'
                        AND (so.is_cancelled = 0 OR so.is_cancelled IS NULL)
                        {$comp_filter_so}
                        AND (DATE(so.date) BETWEEN '{$from_date}' AND '{$to_date}')
                      GROUP BY DATE(so.date)
                      ORDER BY tx_date ASC";
        $sales_rows = $this->db->query($sales_sql)->result_array();

        // Daily collection breakdown
        $coll_sql = "SELECT 
                         DATE(cp.date) as tx_date,
                         IFNULL(SUM(CASE WHEN cp.payment_type = 'official' AND cp.payment_mode = 'payment' THEN cp.amount 
                                         WHEN cp.payment_type = 'official' AND cp.payment_mode = 'return' THEN -cp.amount 
                                         ELSE 0 END), 0) as white_collection,
                         IFNULL(SUM(CASE WHEN cp.payment_type = 'unofficial' AND cp.payment_mode = 'payment' THEN cp.amount 
                                         WHEN cp.payment_type = 'unofficial' AND cp.payment_mode = 'return' THEN -cp.amount 
                                         ELSE 0 END), 0) as black_collection,
                         IFNULL(SUM(CASE WHEN cp.payment_mode = 'payment' THEN cp.amount 
                                         WHEN cp.payment_mode = 'return' THEN -cp.amount 
                                         ELSE 0 END), 0) as total_collection
                     FROM customer_payment cp
                     WHERE cp.is_deleted = 0
                       {$comp_filter_cp}
                       AND (DATE(cp.date) BETWEEN '{$from_date}' AND '{$to_date}')
                     GROUP BY DATE(cp.date)
                     ORDER BY tx_date ASC";
        $coll_rows = $this->db->query($coll_sql)->result_array();

        // Determine whether to group by day or by month
        $curr = strtotime($from_date);
        $last = strtotime($to_date);
        $diff_days = ($last - $curr) / 86400;

        $is_monthly = ($diff_days > 45);
        $daily_map = [];

        if ($is_monthly) {
            $sales_sql = "SELECT 
                              DATE_FORMAT(so.date, '%Y-%m') as tx_period,
                              IFNULL(SUM(so.net_sales_value_1), 0) as white_sales,
                              IFNULL(SUM(so.total_black_amt), 0) as black_sales,
                              IFNULL(SUM(so.grand_total), 0) as total_sales
                          FROM sales_order so
                          WHERE so.is_deleted = '0'
                            AND (so.is_cancelled = 0 OR so.is_cancelled IS NULL)
                            {$comp_filter_so}
                            AND (DATE(so.date) BETWEEN '{$from_date}' AND '{$to_date}')
                          GROUP BY DATE_FORMAT(so.date, '%Y-%m')
                          ORDER BY tx_period ASC";
            $sales_rows = $this->db->query($sales_sql)->result_array();

            $coll_sql = "SELECT 
                             DATE_FORMAT(cp.date, '%Y-%m') as tx_period,
                             IFNULL(SUM(CASE WHEN cp.payment_type = 'official' AND cp.payment_mode = 'payment' THEN cp.amount 
                                             WHEN cp.payment_type = 'official' AND cp.payment_mode = 'return' THEN -cp.amount 
                                             ELSE 0 END), 0) as white_collection,
                             IFNULL(SUM(CASE WHEN cp.payment_type = 'unofficial' AND cp.payment_mode = 'payment' THEN cp.amount 
                                             WHEN cp.payment_type = 'unofficial' AND cp.payment_mode = 'return' THEN -cp.amount 
                                             ELSE 0 END), 0) as black_collection,
                             IFNULL(SUM(CASE WHEN cp.payment_mode = 'payment' THEN cp.amount 
                                             WHEN cp.payment_mode = 'return' THEN -cp.amount 
                                             ELSE 0 END), 0) as total_collection
                         FROM customer_payment cp
                         WHERE cp.is_deleted = 0
                           {$comp_filter_cp}
                           AND (DATE(cp.date) BETWEEN '{$from_date}' AND '{$to_date}')
                         GROUP BY DATE_FORMAT(cp.date, '%Y-%m')
                         ORDER BY tx_period ASC";
            $coll_rows = $this->db->query($coll_sql)->result_array();

            $iter = strtotime(date('Y-m-01', $curr));
            $end_iter = strtotime(date('Y-m-01', $last));
            while ($iter <= $end_iter) {
                $p_str = date('Y-m', $iter);
                $daily_map[$p_str] = [
                    'date'             => date('M Y', $iter),
                    'full_date'        => $p_str,
                    'white_sales'      => 0.0,
                    'black_sales'      => 0.0,
                    'total_sales'      => 0.0,
                    'white_collection' => 0.0,
                    'black_collection' => 0.0,
                    'total_collection' => 0.0,
                ];
                $iter = strtotime('+1 month', $iter);
            }

            foreach ($sales_rows as $s) {
                $p = $s['tx_period'];
                if (isset($daily_map[$p])) {
                    $daily_map[$p]['white_sales'] = (float)$s['white_sales'];
                    $daily_map[$p]['black_sales'] = (float)$s['black_sales'];
                    $daily_map[$p]['total_sales'] = (float)$s['total_sales'];
                }
            }

            foreach ($coll_rows as $c) {
                $p = $c['tx_period'];
                if (isset($daily_map[$p])) {
                    $daily_map[$p]['white_collection'] = (float)$c['white_collection'];
                    $daily_map[$p]['black_collection'] = (float)$c['black_collection'];
                    $daily_map[$p]['total_collection'] = (float)$c['total_collection'];
                }
            }
        } else {
            while ($curr <= $last) {
                $d_str = date('Y-m-d', $curr);
                $daily_map[$d_str] = [
                    'date'             => date('d M', $curr),
                    'full_date'        => $d_str,
                    'white_sales'      => 0.0,
                    'black_sales'      => 0.0,
                    'total_sales'      => 0.0,
                    'white_collection' => 0.0,
                    'black_collection' => 0.0,
                    'total_collection' => 0.0,
                ];
                $curr = strtotime('+1 day', $curr);
            }

            foreach ($sales_rows as $s) {
                $dt = $s['tx_date'];
                if (isset($daily_map[$dt])) {
                    $daily_map[$dt]['white_sales'] = (float)$s['white_sales'];
                    $daily_map[$dt]['black_sales'] = (float)$s['black_sales'];
                    $daily_map[$dt]['total_sales'] = (float)$s['total_sales'];
                }
            }

            foreach ($coll_rows as $c) {
                $dt = $c['tx_date'];
                if (isset($daily_map[$dt])) {
                    $daily_map[$dt]['white_collection'] = (float)$c['white_collection'];
                    $daily_map[$dt]['black_collection'] = (float)$c['black_collection'];
                    $daily_map[$dt]['total_collection'] = (float)$c['total_collection'];
                }
            }
        }

        return array_values($daily_map);
    }

    /**
     * Get Top Selling Products (White Qty, Black Qty, Total Amount)
     */
    public function get_top_products($company_id, $from_date, $to_date, $limit = 5)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND so.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        $sql = "SELECT 
                    sop.product_name,
                    sop.item_code,
                    IFNULL(SUM(sob.white_qty), 0) as white_qty,
                    IFNULL(SUM(sob.black_qty), 0) as black_qty,
                    IFNULL(SUM(sob.white_qty + sob.black_qty), 0) as total_qty,
                    IFNULL(SUM(sob.bill_total + sob.gst_amount), 0) as white_amount,
                    IFNULL(SUM(sob.black_total), 0) as black_amount,
                    IFNULL(SUM(sob.final_total), 0) as total_amount
                FROM sales_order_product_batch sob
                INNER JOIN sales_order so ON so.id = sob.order_id
                INNER JOIN sales_order_product sop ON sop.id = sob.order_product_id
                WHERE so.is_deleted = '0'
                  AND (so.is_cancelled = 0 OR so.is_cancelled IS NULL)
                  {$comp_filter}
                  AND (DATE(so.date) BETWEEN '{$from_date}' AND '{$to_date}')
                GROUP BY sop.product_name, sop.item_code
                ORDER BY total_amount DESC
                LIMIT {$limit}";

        $res = $this->db->query($sql)->result_array();

        if (empty($res)) {
            // Fallback to sales_order_product
            $fallback_sql = "SELECT 
                                sop.product_name,
                                sop.item_code,
                                IFNULL(SUM(sop.qty), 0) as white_qty,
                                0 as black_qty,
                                IFNULL(SUM(sop.qty), 0) as total_qty,
                                IFNULL(SUM(sop.total_amount), 0) as white_amount,
                                0 as black_amount,
                                IFNULL(SUM(sop.total_amount), 0) as total_amount
                            FROM sales_order_product sop
                            INNER JOIN sales_order so ON so.id = sop.order_id
                            WHERE so.is_deleted = '0'
                              AND (so.is_cancelled = 0 OR so.is_cancelled IS NULL)
                              {$comp_filter}
                              AND (DATE(so.date) BETWEEN '{$from_date}' AND '{$to_date}')
                            GROUP BY sop.product_name, sop.item_code
                            ORDER BY total_amount DESC
                            LIMIT {$limit}";
            $res = $this->db->query($fallback_sql)->result_array();
        }

        return $res;
    }

    /**
     * Get Top Customers by Sales Volume & Collections
     */
    public function get_top_customers($company_id, $from_date, $to_date, $limit = 5)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND so.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        $sql = "SELECT 
                    so.customer_id,
                    so.customer_name,
                    COUNT(DISTINCT so.id) as order_count,
                    IFNULL(SUM(so.net_sales_value_1), 0) as white_sales,
                    IFNULL(SUM(so.total_black_amt), 0) as black_sales,
                    IFNULL(SUM(so.grand_total), 0) as total_sales
                FROM sales_order so
                WHERE so.is_deleted = '0'
                  AND (so.is_cancelled = 0 OR so.is_cancelled IS NULL)
                  {$comp_filter}
                  AND (DATE(so.date) BETWEEN '{$from_date}' AND '{$to_date}')
                GROUP BY so.customer_id, so.customer_name
                ORDER BY total_sales DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->result_array();
    }

    /**
     * Get Recent Sales Orders
     */
    public function get_recent_sales_orders($company_id, $limit = 8)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND so.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        $sql = "SELECT 
                    so.id,
                    so.order_no,
                    so.invoice_no,
                    so.date,
                    so.customer_id,
                    so.customer_name,
                    so.net_sales_value_1 as white_amt,
                    so.total_black_amt as black_amt,
                    so.grand_total,
                    so.is_approved,
                    so.is_generated
                FROM sales_order so
                WHERE so.is_deleted = '0'
                  AND (so.is_cancelled = 0 OR so.is_cancelled IS NULL)
                  {$comp_filter}
                ORDER BY so.date DESC, so.id DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->result_array();
    }

    /**
     * Get Recent Payment Collections
     */
    public function get_recent_collections($company_id, $limit = 8)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND cp.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        $sql = "SELECT 
                    cp.id,
                    cp.date,
                    cp.inv_no,
                    cp.customer_id,
                    cp.customer_name,
                    cp.amount,
                    cp.payment_type,
                    cp.payment_method,
                    cp.payment_mode
                FROM customer_payment cp
                WHERE cp.is_deleted = 0
                  {$comp_filter}
                ORDER BY cp.date DESC, cp.id DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->result_array();
    }
}
