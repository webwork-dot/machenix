<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Purchase_dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        /* Cache control */
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');

        $this->load->model('Purchase_dashboard_model', 'purchase_dashboard_model');
        $this->load->model('common_model');
    }

    private function _check_auth()
    {
        if (
            $this->session->userdata('inventory_login') != true &&
            $this->session->userdata('admin_login') != true &&
            $this->session->userdata('super_login') != true
        ) {
            redirect(site_url('login'), 'refresh');
        }
    }

    private function _get_active_company_id()
    {
        $company_id = $this->session->userdata('company_id');
        if (empty($company_id) || $company_id == 0) {
            $companies = $this->common_model->getSessionCompanies();
            if (!empty($companies)) {
                $company_id = (int)$companies[0]['id'];
                $this->session->set_userdata('company_id', $company_id);
            } else {
                $company_id = 0;
            }
        }
        return (int)$company_id;
    }

    private function _parse_date($date_str)
    {
        $date_str = trim($date_str);
        if (empty($date_str)) {
            return null;
        }

        $formats = ['d-m-Y', 'd/m/Y', 'Y-m-d', 'Y/m/d', 'd M, Y', 'd M Y'];
        foreach ($formats as $fmt) {
            $d = DateTime::createFromFormat($fmt, $date_str);
            if ($d && $d->format($fmt) === $date_str) {
                return $d->format('Y-m-d');
            }
        }

        $timestamp = strtotime($date_str);
        if ($timestamp !== false && $timestamp > 0) {
            return date('Y-m-d', $timestamp);
        }

        return null;
    }

    private function _parse_date_filters()
    {
        $date_range  = $this->input->get('date_range') ?: $this->input->post('date_range');
        $custom_from = $this->input->get('from_date') ?: $this->input->post('from_date');
        $custom_to   = $this->input->get('to_date') ?: $this->input->post('to_date');
        $period      = $this->input->get('period') ?: ($this->input->post('period') ?: '');

        $today = date('Y-m-d');
        // Default to current month: 1st of this month to today
        $from_date = date('Y-m-01');
        $to_date   = $today;
        $is_filtered = false;

        if (!empty($date_range)) {
            $parts = explode(' - ', trim($date_range));
            if (count($parts) >= 2) {
                $p_from = $this->_parse_date($parts[0]);
                $p_to   = $this->_parse_date($parts[1]);
                if ($p_from) $from_date = $p_from;
                if ($p_to)   $to_date   = $p_to;
                $is_filtered = true;
                $period = 'custom';
            } elseif (count($parts) == 1 && !empty($parts[0])) {
                $p_from = $this->_parse_date($parts[0]);
                if ($p_from) {
                    $from_date = $p_from;
                    $to_date   = $p_from;
                    $is_filtered = true;
                    $period = 'custom';
                }
            }
        } elseif (!empty($custom_from) && !empty($custom_to)) {
            $p_from = $this->_parse_date($custom_from);
            $p_to   = $this->_parse_date($custom_to);
            if ($p_from) $from_date = $p_from;
            if ($p_to)   $to_date   = $p_to;
            $is_filtered = true;
            $period = 'custom';
        } elseif (!empty($period)) {
            switch ($period) {
                case 'today':
                    $from_date = $today;
                    $to_date   = $today;
                    $is_filtered = true;
                    break;
                case 'yesterday':
                    $from_date = date('Y-m-d', strtotime('-1 day'));
                    $to_date   = $from_date;
                    $is_filtered = true;
                    break;
                case 'this_week':
                    $from_date = date('Y-m-d', strtotime('monday this week'));
                    $to_date   = $today;
                    $is_filtered = true;
                    break;
                case 'last_week':
                    $from_date = date('Y-m-d', strtotime('monday last week'));
                    $to_date   = date('Y-m-d', strtotime('sunday last week'));
                    $is_filtered = true;
                    break;
                case 'this_month':
                    $from_date = date('Y-m-01');
                    $to_date   = $today;
                    break;
                case 'last_month':
                    $from_date = date('Y-m-01', strtotime('first day of last month'));
                    $to_date   = date('Y-m-t', strtotime('last day of last month'));
                    $is_filtered = true;
                    break;
                case 'this_year':
                    $from_date = date('Y-01-01');
                    $to_date   = $today;
                    $is_filtered = true;
                    break;
                case 'all_time':
                    $from_date = '2020-01-01';
                    $to_date   = $today;
                    $is_filtered = true;
                    break;
            }
        }

        // Guarantee from_date <= to_date
        if ($from_date > $to_date) {
            $temp = $from_date;
            $from_date = $to_date;
            $to_date = $temp;
        }

        return [
            'period'         => $period ?: 'this_month',
            'from_date'      => $from_date,
            'to_date'        => $to_date,
            'date_range'     => date('d M, Y', strtotime($from_date)) . ' - ' . date('d M, Y', strtotime($to_date)),
            'raw_date_range' => !empty($date_range) ? $date_range : (date('d-m-Y', strtotime($from_date)) . ' - ' . date('d-m-Y', strtotime($to_date))),
            'is_filtered'    => $is_filtered,
        ];
    }

    public function index()
    {
        $this->_check_auth();

        $company_id = $this->_get_active_company_id();
        $filters    = $this->_parse_date_filters();

        // 1. Consolidated Overview (Import + Local, White & Black)
        $overview = $this->purchase_dashboard_model->get_purchase_overview_summary($company_id, $filters['from_date'], $filters['to_date']);

        // 2. Import Purchase Summary
        $import_summary = $this->purchase_dashboard_model->get_import_purchase_summary($company_id, $filters['from_date'], $filters['to_date']);

        // 3. Local Purchase Summary
        $local_summary = $this->purchase_dashboard_model->get_local_purchase_summary($company_id, $filters['from_date'], $filters['to_date']);

        // 4. PO Expense (Official White & Unofficial Black)
        $expense_summary = $this->purchase_dashboard_model->get_po_expense_summary($company_id, $filters['from_date'], $filters['to_date']);

        // 5. Supplier Payments Summary (Local & Import Suppliers, type = 'payment', Official vs Unofficial)
        $supplier_payments = $this->purchase_dashboard_model->get_supplier_payments_summary($company_id, $filters['from_date'], $filters['to_date']);

        // 5b. Vendor Payments Summary (Operational / PO Expense Vendors, Official vs Unofficial)
        $vendor_payments = $this->purchase_dashboard_model->get_vendor_payments_summary($company_id, $filters['from_date'], $filters['to_date']);

        // 6. Supplier Outstanding Summary (Current Snapshot, Ledger Based)
        $supplier_outstanding = $this->purchase_dashboard_model->get_supplier_outstanding_summary($company_id);

        // 6b. Vendor Outstanding Summary (Current Snapshot, Ledger Based)
        $vendor_outstanding = $this->purchase_dashboard_model->get_vendor_outstanding_summary($company_id);

        // 7. Active Company Procurement Channel Breakdown Matrix
        $channel_matrix = $this->purchase_dashboard_model->get_active_company_channel_matrix($company_id, $filters['from_date'], $filters['to_date']);

        // 8. Daily Trend for Charts (ApexCharts)
        $daily_trend = $this->purchase_dashboard_model->get_daily_trend($company_id, $filters['from_date'], $filters['to_date']);

        // 9. Top Suppliers by Purchase Volume
        $top_suppliers = $this->purchase_dashboard_model->get_top_suppliers($company_id, $filters['from_date'], $filters['to_date'], 5);

        // 10. Top Outstanding Suppliers (Payables)
        $top_outstanding = $this->purchase_dashboard_model->get_top_outstanding_suppliers($company_id, 5);

        // 11. Recent Transactions
        $recent_purchases = $this->purchase_dashboard_model->get_recent_purchase_orders($company_id, 8);
        $recent_payments  = $this->purchase_dashboard_model->get_recent_supplier_payments($company_id, 8);

        // Active Company Details
        $company_info = $this->common_model->getRowById('company', 'id, name, email, contact_no', ['id' => $company_id]);

        $page_data = [
            'page_name'            => 'purchase_dashboard',
            'page_title'           => 'Purchase Dashboard',
            'navigation'           => 'purchase_dashboard',
            'company_id'           => $company_id,
            'company_info'         => $company_info,
            'filters'              => $filters,
            'overview'             => $overview,
            'import_summary'       => $import_summary,
            'local_summary'        => $local_summary,
            'expense_summary'      => $expense_summary,
            'supplier_payments'    => $supplier_payments,
            'vendor_payments'      => $vendor_payments,
            'supplier_outstanding' => $supplier_outstanding,
            'vendor_outstanding'   => $vendor_outstanding,
            'payment_summary'      => $supplier_payments,
            'outstanding_summary'  => $supplier_outstanding,
            'channel_matrix'       => $channel_matrix,
            'daily_trend'          => $daily_trend,
            'top_suppliers'        => $top_suppliers,
            'top_outstanding'      => $top_outstanding,
            'recent_purchases'     => $recent_purchases,
            'recent_payments'      => $recent_payments,
        ];

        $this->load->view('backend/index', $page_data);
    }

    /**
     * AJAX Endpoint for dynamic filter updates without full page reload
     */
    public function get_data_ajax()
    {
        $this->_check_auth();

        $company_id = $this->_get_active_company_id();
        $filters    = $this->_parse_date_filters();

        $overview             = $this->purchase_dashboard_model->get_purchase_overview_summary($company_id, $filters['from_date'], $filters['to_date']);
        $import_summary       = $this->purchase_dashboard_model->get_import_purchase_summary($company_id, $filters['from_date'], $filters['to_date']);
        $local_summary        = $this->purchase_dashboard_model->get_local_purchase_summary($company_id, $filters['from_date'], $filters['to_date']);
        $expense_summary      = $this->purchase_dashboard_model->get_po_expense_summary($company_id, $filters['from_date'], $filters['to_date']);
        $supplier_payments    = $this->purchase_dashboard_model->get_supplier_payments_summary($company_id, $filters['from_date'], $filters['to_date']);
        $vendor_payments      = $this->purchase_dashboard_model->get_vendor_payments_summary($company_id, $filters['from_date'], $filters['to_date']);
        $supplier_outstanding = $this->purchase_dashboard_model->get_supplier_outstanding_summary($company_id);
        $vendor_outstanding   = $this->purchase_dashboard_model->get_vendor_outstanding_summary($company_id);
        $channel_matrix       = $this->purchase_dashboard_model->get_active_company_channel_matrix($company_id, $filters['from_date'], $filters['to_date']);
        $daily_trend          = $this->purchase_dashboard_model->get_daily_trend($company_id, $filters['from_date'], $filters['to_date']);
        $top_suppliers        = $this->purchase_dashboard_model->get_top_suppliers($company_id, $filters['from_date'], $filters['to_date'], 5);
        $top_outstanding      = $this->purchase_dashboard_model->get_top_outstanding_suppliers($company_id, 5);

        $response = [
            'status'               => 'success',
            'filters'              => $filters,
            'overview'             => $overview,
            'import_summary'       => $import_summary,
            'local_summary'        => $local_summary,
            'expense_summary'      => $expense_summary,
            'supplier_payments'    => $supplier_payments,
            'vendor_payments'      => $vendor_payments,
            'supplier_outstanding' => $supplier_outstanding,
            'vendor_outstanding'   => $vendor_outstanding,
            'payment_summary'      => $supplier_payments,
            'outstanding_summary'  => $supplier_outstanding,
            'channel_matrix'       => $channel_matrix,
            'daily_trend'          => $daily_trend,
            'top_suppliers'        => $top_suppliers,
            'top_outstanding'      => $top_outstanding,
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
