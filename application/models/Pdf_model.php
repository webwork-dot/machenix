<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pdf_model extends CI_Model{
        
    public function get_dss_invoice($parent_id,$id) {
        $this->db->where('parent_id', $parent_id);
        $this->db->where('id', $id);
        $query = $this->db->get('dss_invoice');
        return $query;
    }  

	public function update_invoice_generated($id, $file_url){
        $data_order = array(
            'bill_url' => $file_url,
            'is_invoice' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        );
        $this->db->where('id', $id);
        $this->db->update('dss_invoice', $data_order);
    }
        
    public function check_invoice($id){
        $this->db->select('id,bill_no');
        $this->db->where('bill_url!=', NULL);
        $this->db->where('id', $id);
        $query = $this->db->get('dss_invoice');
        return $query;
    }   

   public function update_download_info($id){
        $data_order = array();
        $data_order = array(
            'last_download' => date('Y-m-d H:i:s')
        );		
		$this->db->set('total_download', 'total_download+1', FALSE);
		$this->db->where('id', $id);
		$this->db->update('dss_invoice',$data_order);	
		
    }
	
	public function view_purchase_order($id) { 
        $html = '';
		$signature = 'https://crm.flashpointretail.in/uploads/user_image/signature.png';
        $data = $this->inventory_model->get_puchase_order_details($id);
        //echo json_encode($data);
       // exit();
        $html.= '<html xmlns="http://www.w3.org/1999/xhtml" moznomarginboxes="" mozdisallowselectionprint=""><head>
    <title>Invoice</title>
    <link rel="stylesheet" href="'.base_url().'assets/pdf/bootstrap.min.css">
    <link rel="stylesheet" href="'.base_url().'assets/pdf/custom.css">
  </head>
  <body>
    <div style="background: none repeat scroll 0 0 #ffffff;margin: 0 auto;width: 100%;padding: 0px;">
      <table style="width: 100%;">
        <tbody>
			<tr>
				<td style="width:100%;text-align: center;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="7">
					<b style="font-size: 15px;color: #000;">PURCHASE ORDER</b>
				</td>
			</tr>
          <tr>
            <td style="width:30%;text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="3" rowspan="4">
              <span style="color: #6e6e6e;font-size: 12px;">Invoice To</span><br>
			  <span style="color: #1a1a1a;font-size: 12px;">
					<b>'.$data['company_name'].'</b><br>
			  </span>
			  <span style="color: #6e6e6e;font-size: 12px;">'.$data['company_address'].'</span><br>
			   <span style="color: #6e6e6e;font-size: 12px;">GSTIN/UIN : '.$data['company_gst_no'].'</span><br>
			   <span style="color: #6e6e6e;font-size: 12px;">State Name : '.$data['company_state'].', Code : '.$data['company_state_code'].'</span><br>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #6e6e6e;font-size: 12px;">Voucher No.</span><br>
			  <span style="color: #1a1a1a;font-size: 12px;">
					<b>'.$data['voucher_no'].'</b><br>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #6e6e6e;font-size: 12px;">Date</span><br>
			  <span style="color: #1a1a1a;font-size: 12px;">
					<b>'.$data['date'].'</b><br>
			  </span>
            </td>
          </tr>
		  <tr>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #6e6e6e;font-size: 12px;">Terms of Delivery</span><br>
			  <span style="color: #1a1a1a;font-size: 12px;">
					<b>'.$data['terms_of_delivery'].'</b><br>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #6e6e6e;font-size: 12px;">Mode / Terms of Payment</span><br>
			  <span style="color: #1a1a1a;font-size: 12px;">
					<b>'.$data['mode_of_payment'].'</b><br>
			  </span>
            </td>
          </tr>
		  <tr>
			<td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #6e6e6e;font-size: 12px;">Refrence No.</span><br>
			  <span style="color: #1a1a1a;font-size: 12px;">
					<b>'.$data['refrence_no'].'</b><br>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #6e6e6e;font-size: 12px;">Other Refrence</span><br>
			  <span style="color: #1a1a1a;font-size: 12px;">
					<b>'.$data['other_refrence'].'</b><br>
			  </span>
            </td>
          </tr>
		  <tr>
			<td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #6e6e6e;font-size: 12px;">Dispatched through</span><br>
			  <span style="color: #1a1a1a;font-size: 12px;">
					<b>'.$data['dispatch'].'</b><br>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #6e6e6e;font-size: 12px;">Destination</span><br>
			  <span style="color: #1a1a1a;font-size: 12px;">
					<b>'.$data['destination'].'</b><br>
			  </span>
            </td>
          </tr>
		  <tr>
            <td style="width:50%;text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="3" rowspan="1">
              <span style="color: #6e6e6e;font-size: 12px;">Consignee (Ship to)</span><br>
			  <span style="color: #1a1a1a;font-size: 12px;margin-bottom:0">
					<b>'.$data['warehouse_gst_name'].'</b><br>
			  </span>
			  <span style="color: #6e6e6e;font-size: 12px;margin-bottom:0">'.$data['delivery_address'].'</span><br>
			   <span style="color: #6e6e6e;font-size: 12px;margin-bottom:0">GSTIN/UIN : '.$data['warehouse_gst_no'].'</span><br>
			   <span style="color: #6e6e6e;font-size: 12px;margin-bottom:0">State Name : '.$data['warehouse_state_name'].', Code : '.$data['warehouse_state_code'].'</span><br>
            </td>
			<td style="width:50%;text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="4" rowspan="1">
              <span style="color: #6e6e6e;font-size: 12px;">Supplier (Bill from)</span><br>
			  <span style="color: #1a1a1a;font-size: 12px;">
					<b>'.$data['supplier_gst_name'].'</b><br>
			  </span>
			  <span style="color: #6e6e6e;font-size: 12px;">'.$data['billing_address'].'</span><br>
			   <span style="color: #6e6e6e;font-size: 12px;">GSTIN/UIN : '.$data['supplier_gst_no'].'</span><br>
			   <span style="color: #6e6e6e;font-size: 12px;">State Name : '.$data['supplier_state_name'].' , Code : '.$data['supplier_state_code'].'</span>
            </td>
          </tr>
        </tbody>
      </table>
	  
	  <table style="width: 100%;">
        <thead>
          <tr>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>S.No.</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Description of Goods</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>HSN Code</b>
			  </span>
            </td>  
			<td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>CTN</b>
			  </span>
            </td>
			<td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Quantity</b>
			  </span>
            </td>
    
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Rate</b>
			  </span>
            </td>
      
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Basic Amount</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Gst (%)</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Gst Amount</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Total Amount</b>
			  </span>
            </td>
          </tr>
		  <thead>
		  <tbody style="">';
		  
		  $total_quantity=0;
		  $total_unit='';
		  
		  if(count($data['product']) > 0){ $i = 1;
			foreach($data['product'] as $pro){  
			if($i==1){ $total_unit=$pro['unit'];}
			if($pro['rate']!=''){ $rate = $pro['rate'].'/'.$pro['unit']; }else{ $rate = '';}
			$total_quantity +=$pro['quantity'];
		  $html.= '<tr>
            <td style="text-align: center;padding: 0px 3px; line-height: 1.0; height: auto;border-left:1px solid;border-right:1px solid;width:20px">
              <span style="color: #1a1a1a;font-size: 12px;">
			  '.$pro['sr_no'].'
			  </span>
            </td>
            <td style="width:150px;text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border-left:1px solid;border-right:1px solid;">
              <span style="color: #1a1a1a;font-size: 12px;">
			  '.$pro['name'].'
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border-left:1px solid;border-right:1px solid;">
              <span style="color: #6e6e6e;font-size: 12px;">
				'.$pro['hsn_code'].'
			  </span>
            </td> 
			<td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border-left:1px solid;border-right:1px solid;width:30px">
              <span style="color: #1a1a1a;font-size: 12px;">
			  '.$pro['cartoon'].'
			  </span>
            </td>
        
			<td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border-left:1px solid;border-right:1px solid;width:50px">
              <span style="color: #1a1a1a;font-size: 12px;">
			  '.$pro['quantity'].'  '.$pro['unit'].'
			  </span>
            </td>
        
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border-left:1px solid;border-right:1px solid;">
              <span style="color: #6e6e6e;font-size: 12px;">
			  '.$rate.'
			  
			  </span>
            </td>
         
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border-left:1px solid;border-right:1px solid;">
              <span style="color: #1a1a1a;font-size: 12px;">
			  '.$pro['basic_amount'].'
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border-left:1px solid;border-right:1px solid;">
              <span style="color: #1a1a1a;font-size: 12px;">
			  '.$pro['gst'].'
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border-left:1px solid;border-right:1px solid;">
              <span style="color: #1a1a1a;font-size: 12px;">
			  '.$pro['gst_amount'].'
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border-left:1px solid;border-right:1px solid;">
              <span style="color: #1a1a1a;font-size: 12px;">
			 '.$pro['total_val'].'
			  </span>
            </td>
          </tr>';
			$i++;}
		  }
		  
		     $html.= '<tr style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>  
			<td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
    
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
      
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
          </tr>';
		  
		    $html.= '<tr style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td> 
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td> 
			<td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>'.$total_quantity.' '.$total_unit.'</b>
			  </span>
            </td>
    
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
      
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
          </tr>';
		  
		$html.= '</tbody></table>
		<table style="width: 100%;">
        <tbody>
			<tr>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: right;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="8">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Total Basic Amount</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #1a1a1a;font-size: 12px;">
			  <b>'.$data['net_sales_value_1'].'</b>
			  </span>
            </td>
          </tr>';
		  if($data['gst_type'] == 'CGST/SGST'){
			$html.= '
			<tr>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: right;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="8">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>CGST</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #1a1a1a;font-size: 12px;">
			  <b>'.$data['cgst_amount'].'</b>
			  </span>
            </td>
          </tr>
			<tr>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: right;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="8">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>SGST</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #1a1a1a;font-size: 12px;">
			  <b>'.$data['sgst_amount'].'</b>
			  </span>
            </td>
          </tr>';  
		  }else{
			$html.= '
			<tr>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: right;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="8">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>IGST</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #1a1a1a;font-size: 12px;">
			  <b>'.$data['igst_amount'].'</b>
			  </span>
            </td>
          </tr>';    
		  } 
		  if($data['transport_charge'] > '0'){
			$html.= '
			<tr>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: right;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="8">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Transport Charges</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #1a1a1a;font-size: 12px;">
			  <b>'.$data['transport_charge'].'</b>
			  </span>
            </td>
          </tr>';      
		  }
		  if($data['transport_gst_type'] == 'Yes'){
			$html.= '
			<tr>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: right;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="8">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Transport Gst</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #1a1a1a;font-size: 12px;">
			  <b>'.$data['transport_gst_amount'].'</b>
			  </span>
            </td>
          </tr>';      
		  }
		  if($data['other_charges_amount'] > '0'){
			$html.= '
			<tr>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: right;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="8">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>'.$data['other_charges_name'].'</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #1a1a1a;font-size: 12px;">
			  <b>'.$data['other_charges_amount'].'</b>
			  </span>
            </td>
          </tr>';      
		  }
		  if($data['round_of'] > 0){
			$html.= '
			<tr>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: right;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="8">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Round Of</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #1a1a1a;font-size: 12px;">
			  <b>'.$data['round_of'].'</b>
			  </span>
            </td>
          </tr>';      
		  }
		  
        $html.=  '<tr>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b></b>
			  </span>
            </td>
            <td style="text-align: right;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="8">
              <span style="color: #6e6e6e;font-size: 12px;">
			  <b>Grand Total</b>
			  </span>
            </td>
            <td style="text-align: left;padding: 0px 3px; line-height: 1.0; height: auto;border:1px solid" colspan="2">
              <span style="color: #1a1a1a;font-size: 12px;">
			  <b>'.$data['grand_total'].'</b>
			  </span>
            </td>
          </tr>
		  <tr>
			<td colspan="5" style="text-align: left;width:50%;padding: 0px 3px; line-height: 1.0; height: auto;border-left:1px solid;border-bottom:1px solid;">
              <span style="color: #6e6e6e;font-size: 12px;">
			  Amount Chargeable (in words)
			  </span><br>
			  <span style="color: #1a1a1a;font-size: 12px;">
			  <b>INR '.rupeesToWords($data['grand_total']).'</b><br><br><br><br><br>
			  </span>
            </td>
			<td colspan="5" style="text-align: right;width:50%;padding: 0px 3px; line-height: 1.0; height: auto;border-right:1px solid;border-bottom:1px solid;">
				<span style="color: #1a1a1a;font-size: 12px;">
					<b>For '.$data['company_name'].'</b>
				</span><br/>
				<img src="'.$signature.'" height="60" style="margin-left: 20px;">
				<br/>
				<span style="color: #6e6e6e;font-size: 12px;">
					Authorised Signatory
				</span>
            </td>
          </tr>
        </tbody>
      </table>
	</div>
</body></html>';
    return $html;
    }
       
}