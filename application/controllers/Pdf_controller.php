<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Pdf_controller extends CI_Controller
{
    function __construct() {
        parent::__construct();
        $this->load->model('pdf_model');
    }
	
 public function test_shipping_bills() {
        $this->load->library('pdf');
        $this->load->library('zip');
        $dss_id=2;
        $ids = array(
            '12'
        );		
		
	  foreach ($ids as $id):                        
			 $row = $this->pdf_model->get_dss_invoice($dss_id,$id)->row();		
			if ($row->bill_url == NULL) {
				$order=$this->crud_model->get_invoice_dss_orders_details_by_id($dss_id,$id);
				$page_data=array();
				$page_data['data'] = $order;
				$filename=$param2;	
				$html_content = $this->load->view('invoice/dss_invoice_bill', $page_data, TRUE);
				
				$this->load->library('pdf');
				
				$this->pdf->set_paper("A4", "portrait");
				$this->pdf->set_option('isHtml5ParserEnabled', TRUE);
				$this->pdf->load_html($html_content);
				$this->pdf->render();
				$pdfname = 'Shipping-Label-' . $shipping_no . '.pdf';
				$this->pdf->stream($pdfname, array(
					"Attachment" => 0
				));
				$output = $this->pdf->output();
		     }
		endforeach;	 
    }
    
    
    public function generate_invoice(){
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array(
                'status' => 400,
                'message' => 'Bad request.'
            ));
        } else {        
                $params = json_decode(file_get_contents('php://input'), TRUE);
			
                 $dss_id = $params['dss_id'];
                 $ids    = $params['id'];
				
			
				//echo $this->db->last_query();exit();
						
                if (!empty($ids)) {
                    $check_label = '0';
                    $this->load->library('pdf');
                    $this->load->library('zip');
                    
                    foreach ($ids as $id):
                        
                         $row = $this->pdf_model->get_dss_invoice($dss_id,$id)->row();
                   
					
                        if ($row->bill_url == NULL) {	
							$order=$this->crud_model->get_invoice_dss_orders_details_by_id($dss_id,$id);
							$page_data=array();
							$html_content='';
							$page_data['data'] = $order;
							$filename=$param2;	
							$html_content = $this->load->view('invoice/dss_invoice_bill', $page_data, TRUE);
														
							$this->load->library('pdf');
							$this->pdf->set_paper("A4", "portrait");
							$this->pdf->set_option('isHtml5ParserEnabled', TRUE);
							$this->pdf->load_html($html_content);
							$this->pdf->render();
                            $pdfname   = 'dss-invoice-' .$row->bill_no . '.pdf';
							//$this->pdf->stream($pdfname, array("Attachment"=>0));
							$output = $this->pdf->output();					
                            $year      = date("Y");
                            $month     = date("m");
                            $day       = date("d");
                            //The folder path for our file should be YYYY/MM/DD
                            $directory = "uploads/ss_order_bills/" . "$year/$month/$day/";
                            
                            //If the directory doesn't already exists.
                            if (!is_dir($directory)) {
                                mkdir($directory, 0755, true);
                            }
                            
                            $file_url = $directory . $pdfname;
                            if (file_put_contents($file_url, $output)) {
                                $this->pdf_model->update_invoice_generated($id, $file_url);
                            }
                            unset($this->pdf);
                            $check_label = '1';
                        }
					endforeach;
                    
                    if ($check_label == 1) {
                        $resultdata = array(
                            'status' => 200,
                            'message' => 'Success',
							
                        );
                    } else {
                        $resultdata = array(
                            'status' => 400,
                            'message' => 'Invoice bill already generated!!'
                        );
                    }
                    
				
                    simple_json_output($resultdata);
                } else {
                    json_output(400, array(
                        'status' => 400,
                        'message' => 'Enter required fields'
                    ));
                }
            
        }
    }
    
    
    
    public function generate_invoice_sq()   {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array(
                'status' => 400,
                'message' => 'Bad request.'
            ));
        } else {
        
				$dss_id  = html_escape($this->input->post('dss_id'));
				$order_arr  = html_escape($this->input->post('order_id'));
             
                if (!empty($order_arr)) {
                    $check_label = '0';
                    foreach ($order_arr as $id) {
                        $invoice_sql = $this->pdf_model->get_dss_invoice($dss_id,$id);
						
					
                        $pass         = 0;
                        if ($invoice_sql->num_rows() > 0) {
                            $invoice_row = $invoice_sql->row();
                            if ($invoice_row->bill_url == '' && $invoice_row->bill_url == NULL) {
                                $pass = 1;
                            } else {
                                //check in folder
                                if (!file_exists(FCPATH . $invoice_row->bill_url)) {
                                    $pass = 1;
                                } else {
                                    $pass = 0;
                                }
                            }
                        } else {
                            $pass = 1;
                        }
					
                        if ($pass == 1) {
                             $data    = array();
                             $curl    = curl_init();
                             $url     = base_url() . "pdf_controller/generate_invoice";
                             $dss_id  = $dss_id;
                             $id 	  = $id;
                    
                            curl_setopt_array($curl, array(
                                CURLOPT_URL => $url,
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => "",
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 50,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "POST",
                                CURLOPT_POSTFIELDS => "{\"dss_id\":\"$dss_id\",\"id\":[\"$id\"]}",
                                CURLOPT_HTTPHEADER => array(
                                    "auth-key: skoozorestapi",
                                    "cache-control: no-cache",
                                    "client-service: frontend-client",
                                    "content-type: application/json"
                                )
                            ));
                            
                            $response = curl_exec($curl);
                            curl_close($curl);
                            $result = json_decode($response, TRUE);
							
                            if ($result['status'] == 200) {
                                $check_label = '1';
                            } else {
                                $check_label = '0';
                            }
                        }
                    }
                    
                    
                    if ($check_label == 1) {
                        $resultdata = array(
                            'status' => 200,
                            'message' => 'Invoice generated successfully!'
                        );
                    } else {
                        $resultdata = array(
                            'status' => 400,
                            'message' => 'Invoice bill already generated!'
                        );
                    }
                    
                    simple_json_output($resultdata);
                } else {
                    json_output(400, array(
                        'status' => 400,
                        'message' => 'Enter required fields'
                    ));
                }
            
        }
    }
	
	
	
	 public function check_download_invoice(){
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array(
                'status' => 400,
                'message' => 'Bad request.'
            ));
        } else {
                          
				$dss_id  = html_escape($this->input->post('dss_id'));
				$order_arr  = html_escape($this->input->post('order_id'));
				
                $flag = 0;
                
                $order_array = array();
                $labels = array();
                foreach ($order_arr as $id) {
					$order_array[]=$id;
                    $order = $this->pdf_model->check_invoice($id);
                    if ($order->num_rows()> 0) {
                        //
                    } else {
						$bill_no=$order->row()->bill_no;
                        $flag     = 1;
                        $labels[] = $bill_no;
                        //break;
                    }
                }
                
                if ($flag > 0 || count($labels)>0) {
                    $all_labels = implode(",", $labels);
                    
                    json_output(200, array(
                        'status' => 400,
                        'message' => 'Following Invoice Not Generated Yet-' . $all_labels
                    ));
                } else {
					
					foreach ($order_arr as $id) {
					  $this->pdf_model->update_download_info($id);
					}
					
                    json_output(200, array(
                        'status' => 200,
                        'message' => 'Invoice Generated Successfully',
                        'order_array' => $order_array,
                    ));
                }
            
        }
    }
    
    
    public function download_invoice(){
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method != 'POST') {
            json_output(400, array(
                'status' => 400,
                'message' => 'Bad request.'
            ));
        } else {       
				/*$dss_id  = html_escape($this->input->post('dss_id'));
				$order_arr  = html_escape($this->input->post('order_id'));*/
								
				$params       = json_decode(file_get_contents('php://input'), TRUE);
                $dss_id      = $params['dss_id'];
                $order_arr = $params['order_id'];			
								
                $this->load->library('zip');
                $this->load->helper('download');
                foreach ($order_arr as $id):
                    $order = $this->pdf_model->get_dss_invoice($dss_id,$id)->row();
					
                    if ($order->bill_url != '' && $order->bill_url != NULL) {
                        $filepath1 = FCPATH. '/' . $order->bill_url;

                        $this->zip->read_file($filepath1);
                    }
                endforeach;
                // Download
                $filename = 'Invoice_' . date('Ymdhis') . '.zip';
                $this->zip->download($filename);   

			  /*  $this->load->helper('download');
				// Set file names and paths
				$filenames = array('file1.txt', 'file2.txt', 'file3.txt');
				$filepaths = array('/path/to/file1.txt', '/path/to/file2.txt', '/path/to/file3.txt');

				// Load the zip library
				$this->load->library('zip');

				// Add files to the zip archive
				for ($i = 0; $i < count($filenames); $i++) {
					$this->zip->add_data($filenames[$i], file_get_contents($filepaths[$i]));
				}

				// Generate the zip archive
				$data = $this->zip->get_zip();

				// Download the zip archive
				$zipname = 'myfiles.zip';
				force_download($zipname, $data);

				// Send response message
				echo 'Files downloaded successfully!';*/

				
        }
    }
    
    
}