<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sales_dashboard extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        /* Cache control */
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');

        $this->load->model('Sales_dashboard_model', 'sales_dashboard_model');
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

        // Standard Indian / UK formats (d-m-Y from daterangepicker) and ISO formats
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

        // 1. Funds On Hand (Current Snapshot)
        $funds_summary = $this->sales_dashboard_model->get_funds_on_hand_summary($company_id);

        // 2. Sales Performance
        $sales_summary = $this->sales_dashboard_model->get_sales_amount_summary($company_id, $filters['from_date'], $filters['to_date']);

        // 3. Stock Summary
        $stock_summary = $this->sales_dashboard_model->get_sales_stock_summary($company_id, $filters['from_date'], $filters['to_date']);

        // 4. Payment Collection Matrix
        $collection_summary = $this->sales_dashboard_model->get_payment_collection_summary($company_id, $filters['from_date'], $filters['to_date']);

        // 5. Daily Trend for Charts
        $daily_trend = $this->sales_dashboard_model->get_daily_trend($company_id, $filters['from_date'], $filters['to_date']);

        // 6. Top Selling Products
        $top_products = $this->sales_dashboard_model->get_top_products($company_id, $filters['from_date'], $filters['to_date'], 5);

        // 7. Top Customers
        $top_customers = $this->sales_dashboard_model->get_top_customers($company_id, $filters['from_date'], $filters['to_date'], 5);

        // 8. Recent Activities
        $recent_sales       = $this->sales_dashboard_model->get_recent_sales_orders($company_id, 8);
        $recent_collections = $this->sales_dashboard_model->get_recent_collections($company_id, 8);

        // Company Details
        $company_info = $this->common_model->getRowById('company', 'id, name, email, contact_no', ['id' => $company_id]);

        $page_data = [
            'page_name'          => 'sales_dashboard',
            'page_title'         => 'Sales Dashboard',
            'navigation'         => 'sales_dashboard',
            'company_id'         => $company_id,
            'company_info'       => $company_info,
            'filters'            => $filters,
            'funds_summary'      => $funds_summary,
            'sales_summary'      => $sales_summary,
            'stock_summary'      => $stock_summary,
            'collection_summary' => $collection_summary,
            'daily_trend'        => $daily_trend,
            'top_products'       => $top_products,
            'top_customers'      => $top_customers,
            'recent_sales'       => $recent_sales,
            'recent_collections' => $recent_collections,
        ];

        $this->load->view('backend/index', $page_data);
    }

    /**
     * AJAX Endpoint for dynamic filter changes without full page reload
     */
    public function get_data_ajax()
    {
        $this->_check_auth();

        $company_id = $this->_get_active_company_id();
        $filters    = $this->_parse_date_filters();

        $sales_summary      = $this->sales_dashboard_model->get_sales_amount_summary($company_id, $filters['from_date'], $filters['to_date']);
        $stock_summary      = $this->sales_dashboard_model->get_sales_stock_summary($company_id, $filters['from_date'], $filters['to_date']);
        $collection_summary = $this->sales_dashboard_model->get_payment_collection_summary($company_id, $filters['from_date'], $filters['to_date']);
        $daily_trend        = $this->sales_dashboard_model->get_daily_trend($company_id, $filters['from_date'], $filters['to_date']);
        $top_products       = $this->sales_dashboard_model->get_top_products($company_id, $filters['from_date'], $filters['to_date'], 5);
        $top_customers      = $this->sales_dashboard_model->get_top_customers($company_id, $filters['from_date'], $filters['to_date'], 5);
        $funds_summary      = $this->sales_dashboard_model->get_funds_on_hand_summary($company_id);

        $response = [
            'status'             => 'success',
            'filters'            => $filters,
            'sales_summary'      => $sales_summary,
            'stock_summary'      => $stock_summary,
            'collection_summary' => $collection_summary,
            'daily_trend'        => $daily_trend,
            'top_products'       => $top_products,
            'top_customers'      => $top_customers,
            'funds_summary'      => $funds_summary,
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }
}
