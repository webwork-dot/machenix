<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pnl_report extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');

        $this->load->model('Pnl_report_model', 'pnl_report_model');
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

    private function _build_page_data($company_id, $filters)
    {
        $purchase   = $this->pnl_report_model->get_purchase_in_summary($company_id, $filters['from_date'], $filters['to_date']);
        $sales      = $this->pnl_report_model->get_sales_summary($company_id, $filters['from_date'], $filters['to_date']);
        $expense    = $this->pnl_report_model->get_po_expense_summary($company_id, $filters['from_date'], $filters['to_date']);
        $returns    = $this->pnl_report_model->get_sales_return_summary($company_id, $filters['from_date'], $filters['to_date']);
        $payments   = $this->pnl_report_model->get_payments_summary($company_id, $filters['from_date'], $filters['to_date']);
        $outstanding = $this->pnl_report_model->get_outstanding_summary($company_id);
        $profit     = $this->pnl_report_model->get_profit_summary($sales, $purchase, $expense, $returns);

        $company_info = $this->common_model->getRowById('company', 'id, name, email, contact_no', ['id' => $company_id]);
        if (empty($company_info)) {
            $company_info = ['id' => $company_id, 'name' => 'Company'];
        }

        return [
            'page_name'          => 'pnl_report',
            'page_title'         => 'P&L Report',
            'navigation'         => 'pnl_report',
            'company_id'         => $company_id,
            'company_info'       => $company_info,
            'filters'            => $filters,
            'purchase_summary'   => $purchase,
            'sales_summary'      => $sales,
            'expense_summary'    => $expense,
            'return_summary'     => $returns,
            'profit_summary'     => $profit,
            'outstanding_summary'=> $outstanding,
            'payments_summary'   => $payments,
            'recent_sales'       => $this->pnl_report_model->get_recent_sales_orders($company_id, 8),
            'recent_purchase_in' => $this->pnl_report_model->get_recent_purchase_in($company_id, 8),
            'recent_expenses'    => $this->pnl_report_model->get_recent_po_expenses($company_id, 8),
            'recent_returns'     => $this->pnl_report_model->get_recent_sales_returns($company_id, 8),
            'recent_payments'    => $this->pnl_report_model->get_recent_payments($company_id, 8),
        ];
    }

    public function index()
    {
        $this->_check_auth();

        $company_id = $this->_get_active_company_id();
        $filters    = $this->_parse_date_filters();
        $page_data  = $this->_build_page_data($company_id, $filters);

        $this->load->view('backend/index', $page_data);
    }

    public function get_data_ajax()
    {
        $this->_check_auth();

        $company_id = $this->_get_active_company_id();
        $filters    = $this->_parse_date_filters();

        $purchase    = $this->pnl_report_model->get_purchase_in_summary($company_id, $filters['from_date'], $filters['to_date']);
        $sales       = $this->pnl_report_model->get_sales_summary($company_id, $filters['from_date'], $filters['to_date']);
        $expense     = $this->pnl_report_model->get_po_expense_summary($company_id, $filters['from_date'], $filters['to_date']);
        $returns     = $this->pnl_report_model->get_sales_return_summary($company_id, $filters['from_date'], $filters['to_date']);
        $payments    = $this->pnl_report_model->get_payments_summary($company_id, $filters['from_date'], $filters['to_date']);
        $outstanding = $this->pnl_report_model->get_outstanding_summary($company_id);
        $profit      = $this->pnl_report_model->get_profit_summary($sales, $purchase, $expense, $returns);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success'             => true,
                'filters'             => $filters,
                'purchase_summary'    => $purchase,
                'sales_summary'       => $sales,
                'expense_summary'     => $expense,
                'return_summary'      => $returns,
                'profit_summary'      => $profit,
                'outstanding_summary' => $outstanding,
                'payments_summary'    => $payments,
            ]));
    }
}
