<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pnl_report_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Purchase_dashboard_model', 'purchase_dashboard_model');
        $this->load->model('Sales_dashboard_model', 'sales_dashboard_model');
    }

    /**
     * Total Purchase (purchase-in only): local POs with delivery_status=purchase_in
     * + import via purchase_in_product on import POs with delivery_status=purchase_in.
     */
    public function get_purchase_in_summary($company_id, $from_date, $to_date)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND po.company_id = '{$company_id}'" : "";

        // Local / company purchase-in
        $local_sql = "SELECT 
                        COUNT(DISTINCT po.id) as total_orders,
                        IFNULL(SUM(po.grand_total), 0) as white_purchase_amount,
                        IFNULL(SUM(po.total_black_amount_summary), 0) as black_purchase_amount,
                        IFNULL(SUM(po.grand_total + po.total_black_amount_summary), 0) as gross_purchase_amount
                      FROM purchase_order po
                      WHERE po.is_deleted = '0'
                        AND po.method IN ('local', 'company')
                        AND po.delivery_status = 'purchase_in'
                        {$comp_filter}
                        AND (DATE(po.date) BETWEEN '{$from_date}' AND '{$to_date}')";
        $local = $this->db->query($local_sql)->row_array();

        // Import purchase-in products (only POs marked purchase_in)
        $import_sql = "SELECT 
                        COUNT(DISTINCT po.id) as total_orders,
                        IFNULL(SUM(pip.official_total_rs), 0) as white_import_inr,
                        IFNULL(SUM(CASE WHEN pip.black_total_price > 0 THEN pip.black_total_price 
                                        ELSE ((pip.actual_qty * pip.actual_inr) - pip.official_total_rs) END), 0) as black_import_inr,
                        IFNULL(SUM(pip.total_amount_usd), 0) as white_usd,
                        IFNULL(SUM(pip.actual_qty * pip.actual_usd), 0) as actual_usd,
                        IFNULL(SUM(pip.official_ci_qty * pip.unit_price_rmb), 0) as white_rmb,
                        IFNULL(SUM(pip.actual_qty * pip.actual_rmb), 0) as actual_rmb
                      FROM purchase_in_product pip
                      INNER JOIN purchase_order po ON po.id = pip.parent_id
                      WHERE po.is_deleted = '0'
                        AND po.method = 'import'
                        AND po.delivery_status = 'purchase_in'
                        {$comp_filter}
                        AND (DATE(po.date) BETWEEN '{$from_date}' AND '{$to_date}')";
        $import = $this->db->query($import_sql)->row_array();

        $local_white  = (float)($local['white_purchase_amount'] ?? 0);
        $local_black  = (float)($local['black_purchase_amount'] ?? 0);
        $local_gross  = (float)($local['gross_purchase_amount'] ?? ($local_white + $local_black));
        $local_orders = (int)($local['total_orders'] ?? 0);

        $imp_white  = (float)($import['white_import_inr'] ?? 0);
        $imp_black  = max(0, (float)($import['black_import_inr'] ?? 0));
        $imp_gross  = $imp_white + $imp_black;
        $imp_orders = (int)($import['total_orders'] ?? 0);

        $white_usd = (float)($import['white_usd'] ?? 0);
        $act_usd   = (float)($import['actual_usd'] ?? 0);
        $white_rmb = (float)($import['white_rmb'] ?? 0);
        $act_rmb   = (float)($import['actual_rmb'] ?? 0);

        return [
            'total_orders'          => $local_orders + $imp_orders,
            'local_orders'          => $local_orders,
            'import_orders'         => $imp_orders,
            'white_purchase_amount' => $local_white + $imp_white,
            'black_purchase_amount' => $local_black + $imp_black,
            'gross_purchase_amount' => $local_gross + $imp_gross,
            'local_gross'           => $local_gross,
            'import_gross'          => $imp_gross,
            'total_usd'             => $act_usd,
            'total_rmb'             => $act_rmb,
            'white_usd'             => $white_usd,
            'black_usd'             => max(0, $act_usd - $white_usd),
            'white_rmb'             => $white_rmb,
            'black_rmb'             => max(0, $act_rmb - $white_rmb),
        ];
    }

    public function get_sales_summary($company_id, $from_date, $to_date)
    {
        return $this->sales_dashboard_model->get_sales_amount_summary($company_id, $from_date, $to_date);
    }

    public function get_sales_return_summary($company_id, $from_date, $to_date)
    {
        return $this->sales_dashboard_model->get_sales_return_summary($company_id, $from_date, $to_date);
    }

    public function get_po_expense_summary($company_id, $from_date, $to_date)
    {
        return $this->purchase_dashboard_model->get_po_expense_summary($company_id, $from_date, $to_date);
    }

    public function get_outstanding_summary($company_id)
    {
        return $this->purchase_dashboard_model->get_supplier_outstanding_summary($company_id);
    }

    /**
     * Combined supplier + vendor payments for the period.
     */
    public function get_payments_summary($company_id, $from_date, $to_date)
    {
        $supplier = $this->purchase_dashboard_model->get_supplier_payments_summary($company_id, $from_date, $to_date);
        $vendor   = $this->purchase_dashboard_model->get_vendor_payments_summary($company_id, $from_date, $to_date);

        return [
            'total_count'          => (int)($supplier['total_count'] ?? 0) + (int)($vendor['total_count'] ?? 0),
            'gross_payment_amount' => (float)($supplier['gross_payment_amount'] ?? 0) + (float)($vendor['gross_payment_amount'] ?? 0),
            'white_payment_amount' => (float)($supplier['white_payment_amount'] ?? 0) + (float)($vendor['white_payment_amount'] ?? 0),
            'black_payment_amount' => (float)($supplier['black_payment_amount'] ?? 0) + (float)($vendor['black_payment_amount'] ?? 0),
            'gross_usd'            => (float)($supplier['gross_usd'] ?? 0) + (float)($vendor['gross_usd'] ?? 0),
            'gross_rmb'            => (float)($supplier['gross_rmb'] ?? 0) + (float)($vendor['gross_rmb'] ?? 0),
            'white_usd'            => (float)($supplier['white_usd'] ?? 0) + (float)($vendor['white_usd'] ?? 0),
            'black_usd'            => (float)($supplier['black_usd'] ?? 0) + (float)($vendor['black_usd'] ?? 0),
            'white_rmb'            => (float)($supplier['white_rmb'] ?? 0) + (float)($vendor['white_rmb'] ?? 0),
            'black_rmb'            => (float)($supplier['black_rmb'] ?? 0) + (float)($vendor['black_rmb'] ?? 0),
            'supplier'             => $supplier,
            'vendor'               => $vendor,
        ];
    }

    /**
     * Profit = Sales − Purchase − PO Expense − Sales Return (gross + White/Black).
     */
    public function get_profit_summary($sales, $purchase, $expense, $returns)
    {
        $sales_gross  = (float)($sales['gross_sales_amount'] ?? 0);
        $sales_white  = (float)($sales['white_sales_amount'] ?? 0);
        $sales_black  = (float)($sales['black_sales_amount'] ?? 0);

        $pur_gross    = (float)($purchase['gross_purchase_amount'] ?? 0);
        $pur_white    = (float)($purchase['white_purchase_amount'] ?? 0);
        $pur_black    = (float)($purchase['black_purchase_amount'] ?? 0);

        $exp_gross    = (float)($expense['gross_expense_amount'] ?? 0);
        $exp_white    = (float)($expense['white_expense_amount'] ?? 0);
        $exp_black    = (float)($expense['black_expense_amount'] ?? 0);

        $ret_gross    = (float)($returns['total_return_amount'] ?? 0);
        $ret_white    = (float)($returns['white_return_amount'] ?? 0);
        $ret_black    = (float)($returns['black_return_amount'] ?? 0);

        return [
            'gross_profit' => $sales_gross - $pur_gross - $exp_gross - $ret_gross,
            'white_profit' => $sales_white - $pur_white - $exp_white - $ret_white,
            'black_profit' => $sales_black - $pur_black - $exp_black - $ret_black,
        ];
    }

    public function get_recent_sales_orders($company_id, $limit = 8)
    {
        return $this->sales_dashboard_model->get_recent_sales_orders($company_id, $limit);
    }

    public function get_recent_purchase_in($company_id, $limit = 8)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND po.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        $sql = "SELECT 
                    po.id,
                    po.voucher_no,
                    po.date,
                    po.method,
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
                    po.grand_total as white_amt,
                    po.total_black_amount_summary as black_amt,
                    (po.grand_total + po.total_black_amount_summary) as total_amt,
                    po.delivery_status
                FROM purchase_order po
                WHERE po.is_deleted = '0'
                  AND po.delivery_status = 'purchase_in'
                  {$comp_filter}
                ORDER BY po.date DESC, po.id DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->result_array();
    }

    public function get_recent_po_expenses($company_id, $limit = 8)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND e.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        $sql = "SELECT 
                    e.id,
                    e.expense_date as date,
                    e.batch_no as voucher_no,
                    e.type,
                    COALESCE(v.name, '—') as vendor_name,
                    e.grand_total,
                    e.usd,
                    e.rmb
                FROM po_expense e
                LEFT JOIN my_companies v ON v.id = e.vendor_id
                WHERE e.is_delete = 0
                  {$comp_filter}
                ORDER BY e.expense_date DESC, e.id DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->result_array();
    }

    public function get_recent_sales_returns($company_id, $limit = 8)
    {
        $company_id = (int)$company_id;
        $comp_filter = !empty($company_id) ? " AND gr.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        $sql = "SELECT 
                    gr.id,
                    gr.date,
                    COALESCE(NULLIF(gr.order_no, ''), CAST(gr.id AS CHAR)) as return_no,
                    COALESCE(gr.customer_name, '—') as customer_name,
                    IFNULL(gr.white_total, 0) as white_total,
                    IFNULL(gr.black_total, 0) as black_total,
                    IFNULL(gr.grand_total, 0) as grand_total
                FROM goods_return gr
                WHERE gr.is_deleted = 0
                  {$comp_filter}
                ORDER BY gr.date DESC, gr.id DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->result_array();
    }

    public function get_recent_payments($company_id, $limit = 8)
    {
        $company_id = (int)$company_id;
        $comp_filter_p = !empty($company_id) ? " AND p.company_id = '{$company_id}'" : "";
        $comp_filter_v = !empty($company_id) ? " AND vp.company_id = '{$company_id}'" : "";
        $limit = (int)$limit;

        $sql = "SELECT * FROM (
                    SELECT 
                        p.id,
                        p.payment_date as date,
                        p.invoice_no as ref_no,
                        p.supplier_name as party_name,
                        'Supplier' as party_type,
                        p.amount_rs as amount,
                        p.amount_dollar as usd,
                        p.amount_rmb as rmb,
                        p.payment_type
                    FROM payments p
                    WHERE p.is_delete = 0
                      AND p.type = 'payment'
                      {$comp_filter_p}
                    UNION ALL
                    SELECT 
                        vp.id,
                        vp.payment_date as date,
                        vp.invoice_no as ref_no,
                        COALESCE(vp.vendor_name, v.name, '—') as party_name,
                        'Vendor' as party_type,
                        vp.inr as amount,
                        vp.usd,
                        vp.rmb,
                        vp.payment_type
                    FROM vendor_payments vp
                    LEFT JOIN my_companies v ON v.id = vp.vendor_id
                    WHERE vp.is_delete = 0
                      {$comp_filter_v}
                ) pay_union
                ORDER BY date DESC, id DESC
                LIMIT {$limit}";

        return $this->db->query($sql)->result_array();
    }
}
