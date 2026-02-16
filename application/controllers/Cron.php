<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {
  public function __construct()
  {
    parent::__construct();

    /*cache control*/
    $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    $this->output->set_header('Pragma: no-cache');
    date_default_timezone_set('Asia/Calcutta'); 
    $this->load->model('cron_model');
  }  
  
    public function hold_order_delete() {
      $this->cron_model->hold_order_delete();
    }
    
    public function auto_staff_attendance() {
      $this->cron_model->auto_staff_attendance();
    }
    
    //web
    public function cashfree_patient_payment_checker() {
      $this->cron_model->cashfree_patient_payment_checker();
    }  
    
    public function manual_camp($camp_id) {
      $this->cron_model->manual_camp($camp_id);
    } 
    
    
    public function update_doctor_sale() {
      $this->cron_model->update_doctor_sale();
    }

    
    public function update_einvoice_token() {
      $this->cron_model->update_einvoice_token();
    } 	
	
	
    public function add_inventory() {
      $this->cron_model->add_inventory();
    } 	
	
    public function update_inventory() {
      $this->cron_model->update_inventory();
    } 	
} 