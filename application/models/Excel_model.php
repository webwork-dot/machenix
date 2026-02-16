<?php
defined('BASEPATH') or exit('No direct script access allowed');


class Excel_model extends CI_Model
{

    private $_batchImport;
     
    function __construct()
    {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }
    
    public function generatePIN($digits = 2){
            $i = 0; //counter
            $pin = ""; //our default pin is blank.
            while($i < $digits){
                //generate a random number between 0 and 9.
                $pin .= mt_rand(0, 9);
                $i++;
            }
            return $pin;
    }  
    
    public function check_email_manager($email,$email_start,$email_end)
    {
		$duplicate_email_check = $this->db->get_where('users', array('email' => $email,'role_id' => 2));
        if ($duplicate_email_check->num_rows() > 0) {
            $pin = $this->generatePIN();
		    $email=$email_start.$pin.$email_end;
            $this->check_email_manager($email,$email_start,$email_end);
        } 
		else {
            return $email;        
        }
    }
        

     public function setBatchImport($batchImport) {
        $this->_batchImport = $batchImport;
    }
 
   	function user_excel_insert()
	{
        $data = $this->_batchImport;
        if($data){
        $this->db->insert_batch('users', $data);
        return $this->db->affected_rows() > 0;
    	}
	}
	
    

}