<?php
defined('BASEPATH') or exit('No direct script access allowed');
// Spreadsheet
require APPPATH . 'third_party/phpspreadsheet/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment as alignment; // Instead PHPExcel_Style_Alignment
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;


class Phpspreadsheet extends CI_Controller{

    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->library('session');
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output ->set_header('Pragma: no-cache');
        date_default_timezone_set('Asia/Calcutta');
        $this->load->model('excel_model');
    }

    // index
    public function index()    {
        $data = array();
    }

    public function sample_product_excel2()
    {
        $spreadsheet = IOFactory::load("assets/sample_products.xlsx");
        
        $sheet = $spreadsheet->getSheet('1');
        $sheet->removeColumnByIndex('1');
        $count    = 1;
        $category = $this->crud_model->get_category_details();
        foreach ($category as $item) {
            $sheet->setCellValue('A' . $count, $item['name']);
            $count++;
        }
        
        $sheet2 = $spreadsheet->getSheet('2');
        $sheet2->removeColumnByIndex('1');
        $count2    = 1;
        $tags_list = $this->crud_model->get_colors();
        foreach ($tags_list->result() as $item2) {
            $name2 = $item2->id . ' | ' . $item2->name;
            $sheet2->setCellValue('A' . $count2, $name2);
            $count2++;
        }
        
        $sheet3 = $spreadsheet->getSheet('3');
        $sheet3->removeColumnByIndex('1');
        $count3     = 1;
        $brand_list = $this->crud_model->get_sizes();
        foreach ($brand_list->result() as $item2) {
            $name2 = $item2->id . ' | ' . $item2->name;
            $sheet3->setCellValue('A' . $count3, $name2);
            $count3++;
        }
        
        $sheet4 = $spreadsheet->getSheet('4');
        $sheet4->removeColumnByIndex('1');
        $count4     = 1;
        $color_list = $this->crud_model->get_warehouse();
        foreach ($color_list->result() as $item4) {
            $name4 = $item4->id . ' | ' . $item4->name;
            $sheet4->setCellValue('A' . $count4, $name4);
            $count4++;
        }
        
        $sheet5 = $spreadsheet->getSheet('5');
        $sheet5->removeColumnByIndex('1');
        $count5        = 1;
        $contains_list = $this->crud_model->get_units();
        foreach ($contains_list->result() as $item5) {
            $name5 = $item5->id . ' | ' . $item5->name;
            $sheet5->setCellValue('A' . $count5, $name5);
            $count5++;
        }
        
        //write it again to Filesystem with the same name (=replace)
        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $writer->save('assets/sample_products.xlsx');
        $this->load->helper('download');
        
        $file     = FCPATH . 'assets/sample_products.xlsx';
        $filename = 'sample_products.xlsx';
        // check file exists    
        if (file_exists($file)) {
            // get file content
            $data = file_get_contents($file);
            //force download
            force_download($filename, $data);
        } else {
            $this->session->set_flashdata('errors', 'There is some error while downloding sample file!');
            redirect($this->agent->referrer());
        }
        
    }

    public function sample_product_excel()
    {
        $spreadsheet = IOFactory::load("assets/sample_products.xlsx");
    
        // Helper to apply dropdown safely
        $applyDropdown = function($sheet, $column = 'A', $formulaRange = '', $fromRow = 2, $toRow = 100) {
            if ($toRow < $fromRow) {
                $toRow = $fromRow + 50; // fallback to avoid C2:C1 error
            }
    
            for ($row = $fromRow; $row <= $toRow; $row++) {
                $validation = $sheet->getCell($column . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1($formulaRange);
            }
        };
    
        // --- Sheet 1: Category ---
        $sheet1 = $spreadsheet->getSheet(1); // index 1
        $sheet1->removeColumnByIndex(1);
        $categories = $this->crud_model->get_category_details();
        $count = 1;
        foreach ($categories as $item) {
            $sheet1->setCellValue('A' . $count, $item['name']);
            $count++;
        }
    
        // --- Sheet 2: Colors ---
        $sheet2 = $spreadsheet->getSheet(2); // index 2
        $sheet2->removeColumnByIndex(1);
        $colors = $this->crud_model->get_colors();
        $count = 1;
        foreach ($colors->result() as $item) {
            $sheet2->setCellValue('A' . $count, $item->id . ' | ' . $item->name);
            $count++;
        }
    
        // --- Sheet 3: Sizes ---
        $sheet3 = $spreadsheet->getSheet(3); // index 3
        $sheet3->removeColumnByIndex(1);
        $sizes = $this->crud_model->get_sizes();
        $count = 1;
        foreach ($sizes->result() as $item) {
            $sheet3->setCellValue('A' . $count, $item->id . ' | ' . $item->name);
            $count++;
        }
    
        // --- Sheet 4: Warehouses ---
        $sheet4 = $spreadsheet->getSheet(4); // index 4
        $sheet4->removeColumnByIndex(1);
        $warehouses = $this->crud_model->get_warehouse();
        $count = 1;
        foreach ($warehouses->result() as $item) {
            $sheet4->setCellValue('A' . $count, $item->id . ' | ' . $item->name);
            $count++;
        }
    
        // --- Sheet 5: Units ---
        $sheet5 = $spreadsheet->getSheet(5); // index 5
        $sheet5->removeColumnByIndex(1);
        $units = $this->crud_model->get_units();
        $count = 1;
        foreach ($units->result() as $item) {
            $sheet5->setCellValue('A' . $count, $item->id . ' | ' . $item->name);
            $count++;
        }
        
        // --- Sheet 6: Status ---
        $sheet6 = $spreadsheet->getSheet(6); // index 6
        $sheet6->removeColumnByIndex(1);
        $units = [['id' => 1, 'name' => 'Active'],['id' => 0, 'name' => 'Inactive']];
        $count = 1;
        foreach ($units as $item) {
            $sheet6->setCellValue('A' . $count, $item['id'] . ' | ' . $item['name']);
            $count++;
        }
        
        // --- Sheet 7: Boolean ---
        $sheet7 = $spreadsheet->getSheet(7); // index 7
        $sheet7->removeColumnByIndex(1);
        $bool = [['name' => 'Yes'],['name' => 'No']];
        $count = 1;
        foreach ($bool as $item) {
            $sheet7->setCellValue('A' . $count, $item['name']);
            $count++;
        }
        
        $mainSheet = $spreadsheet->getSheet(0); // Main sheet (index 0)
        $highestRow = $mainSheet->getHighestRow();
        $minRows = 10000; // or adjust to how many dropdown rows you want
        $toRow = ($highestRow >= 2) ? $highestRow : $minRows;
        
        // Apply dropdowns safely
        $applyDropdown($mainSheet, 'C', 'category!$A$1:$A$100', 2, $toRow);
        $applyDropdown($mainSheet, 'E', 'color!$A$1:$A$100', 2, $toRow);
        $applyDropdown($mainSheet, 'F', 'size!$A$1:$A$100', 2, $toRow);
        $applyDropdown($mainSheet, 'H', 'warehouse!$A$1:$A$100', 2, $toRow);
        $applyDropdown($mainSheet, 'J', 'unit!$A$1:$A$100', 2, $toRow);
        $applyDropdown($mainSheet, 'M', 'status!$A$1:$A$100', 2, $toRow);
        $applyDropdown($mainSheet, 'N', 'boolean!$A$1:$A$100', 2, $toRow);
        $applyDropdown($mainSheet, 'O', 'boolean!$A$1:$A$100', 2, $toRow);
        $applyDropdown($mainSheet, 'P', 'boolean!$A$1:$A$100', 2, $toRow);
        $applyDropdown($mainSheet, 'Q', 'boolean!$A$1:$A$100', 2, $toRow);
        $applyDropdown($mainSheet, 'R', 'boolean!$A$1:$A$100', 2, $toRow);
    
        // Save updated file
        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $writer->save('assets/sample_products.xlsx');
    
        // Download the file
        $this->load->helper('download');
        $file = FCPATH . 'assets/sample_products.xlsx';
        $filename = 'sample_products.xlsx';
    
        if (file_exists($file)) {
            $data = file_get_contents($file);
            force_download($filename, $data);
        } else {
            $this->session->set_flashdata('errors', 'Error while downloading sample file!');
            redirect($this->agent->referrer());
        }
    }
    
    public function sample_product_sales_excel()
    {
        $spreadsheet = IOFactory::load("assets/new_sales_orders.xlsx");
        // Helper to apply dropdown safely
        $applyDropdown = function($sheet, $column = 'A', $formulaRange = '', $fromRow = 2, $toRow = 100) {
            if ($toRow < $fromRow) {
                $toRow = $fromRow + 50; // fallback to avoid C2:C1 error
            }
    
            for ($row = $fromRow; $row <= $toRow; $row++) {
                $validation = $sheet->getCell($column . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1($formulaRange);
            }
        };
    
        // --- Sheet 1: Sizes ---
        $sheet1 = $spreadsheet->getSheet(1); // index 1
        $sheet1->removeColumnByIndex(1);
        $sizes = $this->crud_model->get_sizes();
        $count = 1;
        foreach ($sizes->result() as $item) {
            $sheet1->setCellValue('A' . $count, $item->name);
            $count++;
        }
    
        $mainSheet = $spreadsheet->getSheet(0); // Main sheet (index 0)
        $highestRow = $mainSheet->getHighestRow();
        $minRows = 10000; // or adjust to how many dropdown rows you want
        $toRow = ($highestRow >= 2) ? $minRows : $minRows;
        // $toRow = ($highestRow >= 2) ? $highestRow : $minRows;

        $applyDropdown($mainSheet, 'F', 'size!$A$1:$A$100', 2, $toRow);
    
        // Save updated file
        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $writer->save('assets/new_sales_orders.xlsx');
    
        // Download the file
        $this->load->helper('download');
        $file = FCPATH . 'assets/new_sales_orders.xlsx';
        $filename = 'new_sales_orders.xlsx';
    
        if (file_exists($file)) {
            $data = file_get_contents($file);
            force_download($filename, $data);
        } else {
            $this->session->set_flashdata('errors', 'Error while downloading sample file!');
            redirect($this->agent->referrer());
        }
    }
 
    public function sample_product_return_excel()
    {
        // Load the base file
        $spreadsheet = IOFactory::load(FCPATH . "assets/new_return_stock_items.xlsx");
    
        /**
         * Helper: apply a list dropdown to a column over row range.
         * - $list can be:
         *     - static CSV (e.g., 'A,B,C') -> auto-wrapped in quotes: "A,B,C"
         *     - a formula/range (e.g., '=size!$A$1:$A$100') -> left as-is
         */
        $applyDropdown = function($sheet, $column = 'A', $list = '', $fromRow = 2, $toRow = 100) {
            if ($toRow < $fromRow) {
                $toRow = $fromRow; // keep bounds sane
            }
    
            // Wrap static CSV list in quotes for PhpSpreadsheet/Excel
            if ($list !== '' && $list[0] !== '=' && $list[0] !== '"') {
                $list = '"' . $list . '"';
            }
    
            // Build a template DataValidation
            $template = new DataValidation();
            $template->setType(DataValidation::TYPE_LIST);
            $template->setErrorStyle(DataValidation::STYLE_STOP);
            $template->setAllowBlank(true);
            $template->setShowInputMessage(true);
            $template->setShowErrorMessage(true);
            $template->setShowDropDown(true);
            $template->setFormula1($list);
    
            // Apply to each cell (must clone per cell)
            for ($row = $fromRow; $row <= $toRow; $row++) {
                $cell = $sheet->getCell($column . $row);
                $cell->setDataValidation(clone $template);
            }
        };
    
        // --- Sheet 1 (index 1): fill Sizes list in column A ---
        $sheet1 = $spreadsheet->getSheet(1);
        // Optional: ensure the sheet is titled 'size' (matches the validation formula we'll use)
        $sheet1->setTitle('size');
    
        // Clear column A (so we can rewrite fresh)
        $sheet1->removeColumnByIndex(1);
    
        $sizes = $this->crud_model->get_sizes(); // expects ->result() with id, name
        $r = 1;
        foreach ($sizes->result() as $item) {
            $sheet1->setCellValue('A' . $r, $item->id . ' | ' . $item->name);
            $r++;
        }
        $sizesCount = max(1, $r - 1); // number of items written, at least 1
    
        // --- Main sheet (index 0): apply dropdowns ---
        $mainSheet = $spreadsheet->getSheet(0);
        $highestRow = $mainSheet->getHighestRow();
        $minRows = 10000; // how many rows you want pre-prepared with validation
        $toRow = ($highestRow >= 2) ? $highestRow : $minRows;
    
        // 1) Column C: dropdown from helper sheet range (dynamic end based on sizes count)
        $sizeRange = "=size!\$A\$1:\$A\${$sizesCount}";
        $applyDropdown($mainSheet, 'C', $sizeRange, 2, $toRow);
    
        // 2) Column E: static dropdown
        $applyDropdown($mainSheet, 'E', 'Customer Return,RTO,Cancelled', 2, $toRow);
    
        // Save back to disk (overwrite the same file)
        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $writer->save(FCPATH . 'assets/new_return_stock_items.xlsx');
    
        // Download the file to the user
        $this->load->helper('download');
        $file = FCPATH . 'assets/new_return_stock_items.xlsx';
        $filename = 'new_return_stock_items.xlsx';
    
        if (file_exists($file)) {
            $data = file_get_contents($file);
            force_download($filename, $data);
        } else {
            $this->session->set_flashdata('errors', 'Error while downloading sample file!');
            redirect($this->agent->referrer());
        }
    }

    
    public function sample_product_payment_excel()
    {
        $spreadsheet = IOFactory::load("assets/new_payment_stock_items.xlsx");
        // Helper to apply dropdown safely
        $applyDropdown = function($sheet, $column = 'A', $formulaRange = '', $fromRow = 2, $toRow = 100) {
            if ($toRow < $fromRow) {
                $toRow = $fromRow + 50; // fallback to avoid C2:C1 error
            }
    
            for ($row = $fromRow; $row <= $toRow; $row++) {
                $validation = $sheet->getCell($column . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1($formulaRange);
            }
        };
    
        // --- Sheet 1: Sizes ---
        $sheet1 = $spreadsheet->getSheet(1); // index 1
        $sheet1->removeColumnByIndex(1);
        $sizes = $this->crud_model->get_sizes();
        $count = 1;
        foreach ($sizes->result() as $item) {
            $sheet1->setCellValue('A' . $count, $item->id . ' | ' . $item->name);
            $count++;
        }
    
        $mainSheet = $spreadsheet->getSheet(0); // Main sheet (index 0)
        $highestRow = $mainSheet->getHighestRow();
        $minRows = 10000; // or adjust to how many dropdown rows you want
        $toRow = ($highestRow >= 2) ? $highestRow : $minRows;

        $applyDropdown($mainSheet, 'C', 'size!$A$1:$A$100', 2, $toRow);
    
        // Save updated file
        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $writer->save('assets/new_payment_stock_items.xlsx');
    
        // Download the file
        $this->load->helper('download');
        $file = FCPATH . 'assets/new_payment_stock_items.xlsx';
        $filename = 'new_payment_stock_items.xlsx';
    
        if (file_exists($file)) {
            $data = file_get_contents($file);
            force_download($filename, $data);
        } else {
            $this->session->set_flashdata('errors', 'Error while downloading sample file!');
            redirect($this->agent->referrer());
        }
    }
    
    public function sample_product_damage_excel()
    {
        $spreadsheet = IOFactory::load("assets/new_damage_stock_items.xlsx");
        // Helper to apply dropdown safely
        $applyDropdown = function($sheet, $column = 'A', $formulaRange = '', $fromRow = 2, $toRow = 100) {
            if ($toRow < $fromRow) {
                $toRow = $fromRow + 50; // fallback to avoid C2:C1 error
            }
    
            for ($row = $fromRow; $row <= $toRow; $row++) {
                $validation = $sheet->getCell($column . $row)->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1($formulaRange);
            }
        };
    
        // --- Sheet 1: Sizes ---
        $sheet1 = $spreadsheet->getSheet(1); // index 1
        $sheet1->removeColumnByIndex(1);
        $sizes = $this->crud_model->get_sizes();
        $count = 1;
        foreach ($sizes->result() as $item) {
            $sheet1->setCellValue('A' . $count, $item->id . ' | ' . $item->name);
            $count++;
        }
    
        $mainSheet = $spreadsheet->getSheet(0); // Main sheet (index 0)
        $highestRow = $mainSheet->getHighestRow();
        $minRows = 10000; // or adjust to how many dropdown rows you want
        $toRow = ($highestRow >= 2) ? $highestRow : $minRows;

        $applyDropdown($mainSheet, 'B', 'size!$A$1:$A$100', 2, $toRow);
    
        // Save updated file
        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);
        $writer->save('assets/new_damage_stock_items.xlsx');
    
        // Download the file
        $this->load->helper('download');
        $file = FCPATH . 'assets/new_damage_stock_items.xlsx';
        $filename = 'new_damage_stock_items.xlsx';
    
        if (file_exists($file)) {
            $data = file_get_contents($file);
            force_download($filename, $data);
        } else {
            $this->session->set_flashdata('errors', 'Error while downloading sample file!');
            redirect($this->agent->referrer());
        }
    }

    public function sample_attendance_excel(){
        $this->load->helper('download');
        $file = FCPATH . 'assets/sample_attendance.xlsx';
        $filename = 'sample_attendance.xlsx';
        // check file exists
        if (file_exists($file)){        
            $data = file_get_contents($file);         
            force_download($filename, $data);
        }
        else{
            $this->session->set_flashdata('errors', 'There is some error while downloding sample file!');
            redirect($this->agent->referrer());
        }
    }
	
    public function upload_emp_attendance(){
        $this->load->model('attendance_model');
        $data = array();
        $returnData = array();
        $fetchData = array();

        $month_id=html_escape($this->input->post('month_id'));
        $year=html_escape($this->input->post('year'));
        // If file uploaded
        if (!empty($_FILES['fileURL']['name'])) {
            // get file extension
            $extension = pathinfo($_FILES['fileURL']['name'], PATHINFO_EXTENSION);

            if ($extension == 'xlsx'){
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            else{
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
            // file path
            $spreadsheet = $reader->load($_FILES['fileURL']['tmp_name']);
            $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            // array Count
            $arrayCount = count($allDataInSheet);
            $flag = 0;
            $createArray = array(
                'punch_date',
                'emp_id',
                'name',
                'check_in_date',
                'check_out_date',
                'total_hrs',
                'status',
            );
            $makeArray = array(
                'punch_date' => 'punch_date',
                'emp_id' => 'emp_id',
                'name' => 'name',
                'check_in_date' => 'check_in_date',
                'check_out_date' => 'check_out_date',
                'total_hrs' => 'total_hrs',
                'status' => 'status',
            );
             $is_return=0;
         
            $SheetDataKey = array();
            foreach ($allDataInSheet as $dataInSheet){
                foreach ($dataInSheet as $key => $value){
                    if (in_array(trim($value) , $createArray)){
                        $value = preg_replace('/\s+/', '', $value);
                        $SheetDataKey[trim($value) ] = $key;
                    }
                }
            }
			
            $dataDiff = array_diff_key($makeArray, $SheetDataKey);
            if (empty($dataDiff)) {
                $flag = 1;
            }

            if ($flag == 1) {
                for ($i = 2;$i <= $arrayCount;$i++) {
                    $createArray = array(
						'punch_date',
						'emp_id',
						'name',
						'check_in_date',
						'check_out_date',
						'total_hrs',
						'status',
                    );
                    $punch_date = $SheetDataKey['punch_date'];
                    $emp_id = $SheetDataKey['emp_id'];
                    $name = $SheetDataKey['name'];
                    $check_in_date = $SheetDataKey['check_in_date'];
                    $check_out_date = $SheetDataKey['check_out_date'];
                    $total_hrs = $SheetDataKey['total_hrs'];
                    $status = $SheetDataKey['status'];

                    $punch_date = filter_var(trim($allDataInSheet[$i][$punch_date]), FILTER_SANITIZE_STRING);					
                    $emp_id = filter_var(trim($allDataInSheet[$i][$emp_id]), FILTER_SANITIZE_STRING);
                    $name = filter_var(trim($allDataInSheet[$i][$name]),FILTER_SANITIZE_STRING);
                    $check_in_date = filter_var(trim($allDataInSheet[$i][$check_in_date]), FILTER_SANITIZE_STRING);
                    $check_out_date = filter_var(trim($allDataInSheet[$i][$check_out_date]), FILTER_SANITIZE_STRING);
                    $total_hrs = filter_var(trim($allDataInSheet[$i][$total_hrs]), FILTER_SANITIZE_STRING);
                    $status = filter_var(trim($allDataInSheet[$i][$status]), FILTER_SANITIZE_STRING);
	
                    if ($punch_date != ''){
                        $validity=$this->attendance_model->check_month_validation($punch_date,$month_id,$year);
                        $validity2=$this->attendance_model->check_emp_id($emp_id);
                        if($validity>0){
                          $returnData[] = array(
                            'punch_date' 	=> $punch_date,
                            'emp_id'	 	=> $emp_id,
                            'name'		    => $name,
                            'check_in_date' => $check_in_date,
                            'check_out_date'=> $check_out_date,
                            'total_hrs' 	=> $total_hrs,
                            'status' 		=> $status,
                            'remark' 		=> 'Selected Months Mismatch',
                           );
                          $is_return=1;
                        } 
						elseif($validity2==0){
                          $returnData[] = array(
                            'punch_date' 	=> $punch_date,
                            'emp_id'	 	=> $emp_id,
                            'name'		    => $name,
                            'check_in_date' => $check_in_date,
                            'check_out_date'=> $check_out_date,
                            'total_hrs' 	=> $total_hrs,
                            'status' 		=> $status,
                            'remark' 		=> 'Emp id not found in system',
                           );
                          $is_return=1;
                        }
                        else{
                        $fetchData[] = array(
                            'punch_date' 	=> $punch_date,
                            'emp_id'	 	=> $emp_id,
                            'name'		    => $name,
                            'check_in_date' => $check_in_date,
                            'check_out_date'=> $check_out_date,
                            'total_hrs' 	=> $total_hrs,
                            'status' 		=> $status,
                            'remark' 		=> '',
                        );
                       }

                    }

                }

                $output = '';
		$count_added=count($fetchData);	
     if ($count_ = $this->attendance_model->import_emp_attendance_excel_insert($fetchData,$month_id,$year)){      
        $output .='<div class="row">';
        $output .='<div class="col-md-12">	<div class="card"><div class="card-body"> <h6>Following Attendance Not Added - Check Remark for more info!</h6>';		    
        $output .='<div class="table-responsive">';
        $output .='<table id="example" class="table d-report">';
        $output .='<thead class="fixedHeader">';
        $output .='<tr class="alternateRow"> ';
        $output .='<th>Punch Date</th>';
        $output .='<th>Emp Id</th>';
        $output .='<th>Name</th>';
        $output .='<th>Check In </th>';
        $output .='<th>Check Out</th>';
        $output .='<th>Total Hrs</th>';
        $output .='<th>Status</th>';
        $output .='<th>Remark</th>';
        $output .='</tr>';
        $output .='</thead>';
        $output .='<tbody class="scrollContent">';
        foreach($returnData as $row){	
        $output .='<tr>';
        $output .='<td>'.$row['punch_date'].'</td>';
        $output .='<td>'.$row['emp_id'].'</td>';
        $output .='<td>'.$row['name'].'</td>';
        $output .='<td>'.$row['check_in_date'].'</td>';
        $output .='<td>'.$row['check_out_date'].'</td>';
        $output .='<td>'.$row['total_hrs'].'</td>';  
        $output .='<td>'.$row['status'].'</td>';  
        $output .='<td>'.$row['remark'].'</td>';  
        $output .='</tr>'; 
        }
        $output .='</tbody>';
        $output .='</table>';
        $output .='</div>';
        $output .='</div>';
        $output .='</div>';
        $output .='</div></div>';
        		
        $output .= '<script>$(document).ready(function() {$("#example").DataTable({"ordering": false,"scrollX": true,"scrollY":"500px","scrollCollapse": true,"fixedHeader": true}); }); Swal.fire({title: "Alert!",text: "Some Attendance Not Added!",icon: "warning"});</script>';			 
        if(count($returnData)>0){
        	echo $output;
        }	
        else {
        	echo '<script>Swal.fire({title: "Success!",text: "Attendance Added Successfully!",icon: "success"}).then(() => {location.reload()});</script>';	
         } 		 
        }
        else {		    
        $output .='<div class="row">';
        $output .='<div class="col-md-12">	<div class="card"><div class="card-body"> <h6>Following Attendance Not Added - Check Remark for more info!</h6>';   
        $output .='<div class="table-responsive">';
        $output .='<table id="example" class="table d-report">';
        $output .='<thead class="fixedHeader">';
        $output .='<tr class="alternateRow"> ';
        $output .='<th>Punch Date</th>';
        $output .='<th>Emp Id</th>';
        $output .='<th>Name</th>';
        $output .='<th>Check In </th>';
        $output .='<th>Check Out</th>';
        $output .='<th>Total Hrs</th>';
        $output .='<th>Status</th>';
        $output .='<th>Remark</th>';
        $output .='</tr>';
        $output .='</thead>';
        $output .='<tbody class="scrollContent">';
        foreach($returnData as $row){	
        $output .='<tr>';
        $output .='<td>'.$row['punch_date'].'</td>';
        $output .='<td>'.$row['emp_id'].'</td>';
        $output .='<td>'.$row['name'].'</td>';
        $output .='<td>'.$row['check_in_date'].'</td>';
        $output .='<td>'.$row['check_out_date'].'</td>';
        $output .='<td>'.$row['total_hrs'].'</td>';  
        $output .='<td>'.$row['status'].'</td>';  
        $output .='<td>'.$row['remark'].'</td>';  
        $output .='</tr>'; 
        }
        $output .='</tbody>';
        $output .='</table>';
        $output .='</div>';
        $output .='</div>';
        $output .='</div>';
        $output .='</div></div>';
        		
        $output .= '<script>$(document).ready(function() {$("#example").DataTable({"ordering": false,"scrollX": true,"scrollY":"500px","scrollCollapse": true,"fixedHeader": true});
         Swal.fire({title: "Error!",text: "'.$count_added.' Attendance added!",icon: "error"}); })</script>';	
         
        if(count($returnData)>0){
        	echo $output;
        }	
        else {
        	echo '<script>Swal.fire({title: "Success!",text: "Attendance Added Successfully!",icon: "success"}).then(() => {location.reload()});</script>';	
          } 
         }
         }
         else {
        	echo '<script> Swal.fire({title: "Error!",text: "Please import correct file, did not match excel sheet column!",icon: "error"})</script>';
           }               
        }    
    }
	  
	public function upload_orders(){
        $data = array();
        $returnData = array();
        $fetchData = array();
		$customer_id = $this->input->post('customer_id');
		$warehouse_id = $this->input->post('warehouse_id');
		$company_id = $this->input->post('company_id');
		$refrence_no = $this->input->post('refrence_no');
		$date = $this->input->post('date');
		//echo $customer_id;
		//echo $warehouse_id;
        // If file uploaded
        if (!empty($_FILES['fileURL']['name']))
        {
            // get file extension
            $extension = pathinfo($_FILES['fileURL']['name'], PATHINFO_EXTENSION);
            if ($extension == 'xlsx')
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            else
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
            // file path
            $spreadsheet = $reader->load($_FILES['fileURL']['tmp_name']);
            $allDataInSheet = $spreadsheet->getActiveSheet()
                ->toArray(null, true, true, true);

            // array Count
            $arrayCount = count($allDataInSheet);
          
            $flag = 0;
            $createArray = array("order_id", "customer_name", "pincode", "state", "sku_code", "size", "quantity", "amount");
            $makeArray = array(
                "order_id" => "order_id", 
                "customer_name" => "customer_name", 
                "pincode" => "pincode", 
                "state" => "state", 
                "sku_code" => "sku_code", 
                "size" => "size",
                "quantity" => "quantity", 
                "amount" => "amount",
            );
            
            $is_return=0;
         
            $SheetDataKey = array();
            foreach ($allDataInSheet as $dataInSheet) {
                foreach ($dataInSheet as $key => $value) {
                    if (in_array(trim($value), $createArray)) {
                        $value = preg_replace('/\s+/', '', $value);
                        $SheetDataKey[trim($value)] = $key;
                    } 
                }
            }
            $dataDiff = array_diff_key($makeArray, $SheetDataKey);
            if (empty($dataDiff)){  
                $flag = 1;
            }
            if ($flag==1) {
                for ($i = 2;$i <= $arrayCount;$i++) {  
                    $dispense_date  = $date;
                    $order_id       = filter_var(trim($allDataInSheet[$i][$SheetDataKey['order_id']]), FILTER_SANITIZE_STRING);
                    $customer_name  = filter_var(trim($allDataInSheet[$i][$SheetDataKey['customer_name']]), FILTER_SANITIZE_STRING);
                    $pincode        = filter_var(trim($allDataInSheet[$i][$SheetDataKey['pincode']]), FILTER_SANITIZE_STRING);
                    $state          = filter_var(trim($allDataInSheet[$i][$SheetDataKey['state']]), FILTER_SANITIZE_STRING);
					$sku_code       = filter_var(trim($allDataInSheet[$i][$SheetDataKey['sku_code']]), FILTER_SANITIZE_STRING);
					$size           = filter_var(trim($allDataInSheet[$i][$SheetDataKey['size']]), FILTER_SANITIZE_STRING);
                    $quantity       = filter_var(trim($allDataInSheet[$i][$SheetDataKey['quantity']]), FILTER_SANITIZE_STRING);
                    $amount         = filter_var(trim($allDataInSheet[$i][$SheetDataKey['amount']]), FILTER_SANITIZE_STRING);
					$batch_no       = NULL ;
                    if ($sku_code != ''){
						$fetchData[] = array(
							'dispense_date' => $dispense_date,
                            'sku_code' => $sku_code,
                            'quantity' => $quantity,
                            'amount' => $amount,
                            'customer_name' => $customer_name,
                            'pincode' => $pincode,
                            'state' => $state,
                            'size' => $size,
                            'batch_no' => $batch_no,
                            'order_id' => $order_id,
                            'customer_id' => $customer_id,
                            'warehouse_id' => $warehouse_id,
                            'company_id' => $company_id,
                            'refrence_no' => $refrence_no,
                        );
                    } else {
                        $returnData[] = array(
                            'customer_name' => $customer_name,
                            'order_id' => $order_id,
                            'pincode' => $pincode,
                            'state' => $state,
                            'sku_code' => $sku_code,
                            'size' => $size,
                            'quantity' => $quantity,
                            'amount' => $amount,
                        );
						$is_return=1;
                    }

                }
                $output = '';
                // echo json_encode($fetchData);exit();
				$count_ = $this->inventory_model->import_orders_excel_insert($fetchData);
                // echo json_encode($count_);exit();
				if($count_){
					//echo json_encode($count_);exit();
								 
					if($count_['status'] == '400'){
						$output .='<div class="row">';
					$output .='<div class="col-md-12">	<div class="card"><div class="card-body"> <h6 class="error">Below List Orders Are Not Added - Below Products Are Not Found In Inventory!</h6>';	
						
					$output .='<div class="table-responsive">';
					$output .='<table id="example" class="table d-report">';
					$output .='<thead class="fixedHeader">';
					$output .='<tr class="alternateRow"> ';
					$output .='<th>Sr</th>';
					$output .='<th>Order ID</th>';
					$output .='<th>Customer Name</th>';
					$output .='<th>Pincode</th>';
					$output .='<th>SKU Code</th>';
					$output .='<th>Size</th>';
					$output .='<th>Quantity</th>';
					$output .='<th>Amount</th>';
					$output .='</tr>';
					$output .='</thead>';
					$output .='<tbody class="scrollContent">';
					foreach($count_['returnData'] as $key => $row) { $key++;
						$output .='<tr>';
						$output .='<td>'.$key.'</td>';
						$output .='<td>'.$row['order_id'].'</td>';
						$output .='<td>'.$row['customer_name'].'</td>';
						$output .='<td>'.$row['pincode'].'</td>';
						$output .='<td>'.$row['sku_code'].'</td>';
						$output .='<td>'.$row['size'].'</td>';
						$output .='<td>'.$row['quantity'].'</td>';
						$output .='<td>'.$row['amount'].'</td>';
						$output .='</tr>'; 
					}
					$output .='</tbody>';
					$output .='</table>';
					$output .='</div>';
					
					$output .='</div>';
					$output .='</div>';
					$output .='</div></div>';
					//echo json_encode($count_);exit();		
					$output .= '<script>$(document).ready(function() {$("#example").DataTable({"ordering": false,"scrollX": true,"scrollY":"500px","scrollCollapse": true,"fixedHeader": true}); }); Swal.fire({title: "Alert!",text: "Some Orders Not Added - Some Product Is Not Found In Inventory 1!",icon: "warning"});</script>';
					echo $output;
					}	
					else {
						echo '<script>Swal.fire({title: "Success!",text: "Orders Added Successfully!",icon: "success"}).then(() => {location.reload()});</script>';	
					} 		 
				}
				else {		    
					$output .='<div class="row">';
					$output .='<div class="col-md-12">	<div class="card"><div class="card-body"> <h6>Following Products Not Added - Product SKU Already Exist!</h6>';		    
					$output .='<div class="table-responsive">';
					$output .='<table id="example" class="table d-report">';
					$output .='<thead class="fixedHeader">';
					$output .='<tr class="alternateRow"> ';
					$output .='<th>Order ID</th>';
					$output .='<th>Customer Name</th>';
					$output .='<th>Pincode</th>';
					$output .='<th>SKU Code</th>';
					$output .='<th>Size</th>';
					$output .='<th>Quantity</th>';
					$output .='<th>Amount</th>';
					$output .='</tr>';
					$output .='</thead>';
					$output .='<tbody class="scrollContent">';
					foreach($returnData as $row){
						$output .='<tr>';
						$output .='<td>'.$row['order_id'].'</td>';
						$output .='<td>'.$row['customer_name'].'</td>';
						$output .='<td>'.$row['pincode'].'</td>';
						$output .='<td>'.$row['sku_code'].'</td>';
						$output .='<td>'.$row['size'].'</td>';
						$output .='<td>'.$row['quantity'].'</td>';
						$output .='<td>'.$row['amount'].'</td>';
						$output .='</tr>'; 
					}
					$output .='</tbody>';
					$output .='</table>';
					$output .='</div>';
					$output .='</div>';
					$output .='</div>';
					$output .='</div></div>';
                    
					$output .= '<script>$(document).ready(function() {$("#example").DataTable({"ordering": false,"scrollX": true,"scrollY":"500px","scrollCollapse": true,"fixedHeader": true});
					 Swal.fire({title: "Error!",text: "0 Orders added!",icon: "error"}); })</script>';	
					 
					if(count($returnData)>0){
						echo $output;
					}	
					else {
						echo '<script>Swal.fire({title: "Success!",text: "Orders Added Successfully!",icon: "success"}).then(() => {location.reload()});</script>';	
					} 
				}
			}
			else {
				echo '<script> Swal.fire({title: "Error!",text: "Please import correct file, did not match excel sheet column!",icon: "error"})</script>';
			}               
		}    
	}
	  
	public function check_orders(){
        $data = array();
        $returnData = array();
        $fetchData = array();
		$warehouse_id = $this->input->post('warehouse_id');
		
        // If file uploaded
        if (!empty($_FILES['fileURL']['name']))
        {
            // get file extension
            $extension = pathinfo($_FILES['fileURL']['name'], PATHINFO_EXTENSION);
            if ($extension == 'xlsx')
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            else
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
            // file path
            $spreadsheet = $reader->load($_FILES['fileURL']['tmp_name']);
            $allDataInSheet = $spreadsheet->getActiveSheet()
                ->toArray(null, true, true, true);

            // array Count
            $arrayCount = count($allDataInSheet);
          
            // $createArray = array("dispense_date","sku_code","quantity","amount","order_id");
            // $makeArray = array("dispense_date" => "dispense_date", "sku_code" => "sku_code", "quantity" => "quantity", "amount" => "amount","order_id" => "order_id");
           
            $flag=0;
            $createArray = array("order_id", "customer_name", "pincode", "state", "sku_code", "size", "quantity", "amount");
            $makeArray = array(
                "order_id" => "order_id", 
                "customer_name" => "customer_name", 
                "pincode" => "pincode", 
                "state" => "state", 
                "sku_code" => "sku_code", 
                "size" => "size",
                "quantity" => "quantity", 
                "amount" => "amount",
            );
            
            $is_return=0;
            
            $SheetDataKey = array();
            foreach ($allDataInSheet as $dataInSheet) {
                foreach ($dataInSheet as $key => $value) {
                    if (in_array(trim($value), $createArray)) {
                        $value = preg_replace('/\s+/', '', $value);
                        $SheetDataKey[trim($value)] = $key;
                    } 
                }
            }
            
            $dataDiff = array_diff_key($makeArray, $SheetDataKey);
            if (empty($dataDiff)){  
                $flag = 1;
            }
            if ($flag==1) {
                for ($i = 2;$i <= $arrayCount;$i++) {  
                    
                    $dispense_date  = $date;
                    $order_id       = filter_var(trim($allDataInSheet[$i][$SheetDataKey['order_id']]), FILTER_SANITIZE_STRING);
                    $customer_name  = filter_var(trim($allDataInSheet[$i][$SheetDataKey['customer_name']]), FILTER_SANITIZE_STRING);
                    $pincode        = filter_var(trim($allDataInSheet[$i][$SheetDataKey['pincode']]), FILTER_SANITIZE_STRING);
                    $state          = filter_var(trim($allDataInSheet[$i][$SheetDataKey['state']]), FILTER_SANITIZE_STRING);
					$sku_code       = filter_var(trim($allDataInSheet[$i][$SheetDataKey['sku_code']]), FILTER_SANITIZE_STRING);
					$size           = filter_var(trim($allDataInSheet[$i][$SheetDataKey['size']]), FILTER_SANITIZE_STRING);
                    $quantity       = filter_var(trim($allDataInSheet[$i][$SheetDataKey['quantity']]), FILTER_SANITIZE_STRING);
                    $amount         = filter_var(trim($allDataInSheet[$i][$SheetDataKey['amount']]), FILTER_SANITIZE_STRING);
					$batch_no       = NULL ;
                    if ($sku_code != ''){
						$fetchData[] = array(
							'dispense_date' => $dispense_date,
                            'sku_code' => $sku_code,
                            'quantity' => $quantity,
                            'amount' => $amount,
                            'customer_name' => $customer_name,
                            'pincode' => $pincode,
                            'state' => $state,
                            'size' => $size,
                            'batch_no' => $batch_no,
                            'order_id' => $order_id,
                            'customer_id' => $customer_id,
                            'warehouse_id' => $warehouse_id,
                            'company_id' => $company_id,
                            'refrence_no' => $refrence_no,
                        );
                    } else {
                        $returnData[] = array(
                            'customer_name' => $customer_name,
                            'order_id' => $order_id,
                            'pincode' => $pincode,
                            'state' => $state,
                            'sku_code' => $sku_code,
                            'size' => $size,
                            'quantity' => $quantity,
                            'amount' => $amount,
                        );
						$is_return=1;
                    }
                }
                
                // echo json_encode($fetchData); exit();
                
                $output = '';
				$count = $this->inventory_model->check_imported_product($fetchData, $warehouse_id);
                // echo json_encode($count);exit();
				if(count($count['not_found']) > 0 || count($count['not_enough']) > 0){
					if(count($count['not_found']) > 0){
    					$output .='<div class="row">';
    					$output .='<div class="col-md-12">	<div class="card"><div class="card-body"> <h6 class="error">Products Below Are Not Found In Inventory!</h6>';	
    						
    					$output .='<div class="table-responsive">';
    					$output .='<table id="example" class="table d-report">';
    					$output .='<thead class="fixedHeader">';
    					$output .='<tr class="alternateRow"> ';
    					$output .='<th>Sr</th>';
    					$output .='<th>Product Name</th>';
    					$output .='<th>Size</th>';
    					$output .='<th>Quantity</th>';
    					$output .='</tr>';
    					$output .='</thead>';
    					$output .='<tbody class="scrollContent">';
    					foreach($count['not_found'] as $key => $row) { $key++;
    						$output .='<tr>';
    						$output .='<td>'.$key.'</td>';
    						$output .='<td>'.$row['sku_code'].'</td>';
    						$output .='<td>'.$row['size'].'</td>';
    						$output .='<td>'.$row['quantity'].'</td>';
    						$output .='</tr>'; 
    					}
    					$output .='</tbody>';
    					$output .='</table>';
    					$output .='</div>';
    					
    					$output .='</div>';
    					$output .='</div>';
    					$output .='</div></div>';	
    					$output .= '<script>$(document).ready(function() {$("#example").DataTable({"ordering": false,"scrollX": true,"scrollY":"500px","scrollCollapse": true,"fixedHeader": true}); }); </script>';
					}
					
					if(count($count['not_enough']) > 0) {
    					$output .='<div class="row">';
    					$output .='<div class="col-md-12">	<div class="card"><div class="card-body"> <h6 class="error">Not enough quantity in stock of these Products!</h6>';	
    						
    					$output .='<div class="table-responsive">';
    					$output .='<table id="example2" class="table d-report">';
    					$output .='<thead class="fixedHeader">';
    					$output .='<tr class="alternateRow"> ';
    					$output .='<th>Sr</th>';
    					$output .='<th>Product Name</th>';
    					$output .='<th>Size</th>';
    					$output .='<th>Quantity</th>';
    					$output .='</tr>';
    					$output .='</thead>';
    					$output .='<tbody class="scrollContent">';
    					foreach($count['not_enough'] as $key => $row) { $key++;
    						$output .='<tr>';
    						$output .='<td>'.$key.'</td>';
    						if(isset($row['other_sku'])) {
    						    $output .='<td>'.$row['other_sku'].'</td>';
    						} else {
    						    $output .='<td>'.$row['sku_code'].'</td>';
    						}
    						$output .='<td>'.$row['size'].'</td>';
    						$output .='<td>'.$row['quantity'].'</td>';
    						$output .='</tr>'; 
    					}
    					$output .='</tbody>';
    					$output .='</table>';
    					$output .='</div>';
    					$output .='</div>';
    					$output .='</div>';
    					$output .='</div></div>';	
    					$output .= '<script>$(document).ready(function() {$("#example2").DataTable({"ordering": false,"scrollX": true,"scrollY":"500px","scrollCollapse": true,"fixedHeader": true}); }); document.querySelector("#submit-btn").classList.add("d-none");</script>';
					}
					
					echo $output;
				} else {
					$output .= '<script>document.querySelector("#submit-btn").classList.remove("d-none")</script>';
					echo $output;
				}
			}
			else {
				echo '<script> Swal.fire({title: "Error!",text: "Please import correct file, did not match excel sheet column!",icon: "error"}); document.querySelector("#submit-btn").classList.add("d-none");</script>';
			}               
		}    
	}
	
	public function upload_purchase_order_items(){
        $data = array();
        $returnData = array();
        $fetchData = array();
		//echo json_encode($_FILES['file']['name']);exit();
        if (!empty($_FILES['fileURL']['name'])) {
            // get file extension
            $extension = pathinfo($_FILES['fileURL']['name'], PATHINFO_EXTENSION);
            if ($extension == 'xlsx')
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            else
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
			
            // file path
            $spreadsheet = $reader->load($_FILES['fileURL']['tmp_name']);
            $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
			
            // array Count
            $arrayCount = count($allDataInSheet);
          
            $flag = 0;
            $createArray = array("sku_code","quantity","gst_percentage");
            $makeArray = array("sku_code" => "sku_code","quantity" => "quantity","gst_percentage" => "gst_percentage");
			
            $is_return = 0;
            
            $SheetDataKey = array();
            foreach ($allDataInSheet as $dataInSheet) {
                foreach ($dataInSheet as $key => $value) {
                    if (in_array(trim($value), $createArray)) {
                        $value = preg_replace('/\s+/', '', $value);
                        $SheetDataKey[trim($value)] = $key;
                    } 
                }
            }
			
            $dataDiff = array_diff_key($makeArray, $SheetDataKey);
            if (empty($dataDiff)){
                $flag = 1;
            }
            
            if ($flag == 1) {
                for ($i = 2;$i <= $arrayCount;$i++) {
                    $product_name = filter_var(trim($allDataInSheet[$i][$SheetDataKey['sku_code']]), FILTER_SANITIZE_STRING);
                    $quantity = filter_var(trim($allDataInSheet[$i][$SheetDataKey['quantity']]), FILTER_SANITIZE_STRING);
                    $gst_percentage = filter_var(trim($allDataInSheet[$i][$SheetDataKey['gst_percentage']]), FILTER_SANITIZE_STRING);

                    if ($product_name != ''){
						$fetchData[] = array(
                            'product_name' => $product_name,
                            'quantity' => $quantity,
                            'gst_percentage' => $gst_percentage,
                        );
						
                    }else{
                        $returnData[] = array(
                            'product_name' => $product_name,
                            'quantity' => $quantity,
                            'gst_percentage' => $gst_percentage,
                        );
						$is_return = 1;
                    }
                }
				//echo json_encode($returnData);exit();
                $output = '';
				$count_ = $this->inventory_model->import_purchase_order_items_excel_insert($fetchData);
				return simple_json_output($count_);
			} else {
				echo '<script> Swal.fire({title: "Error!",text: "Please import correct file, did not match excel sheet column!",icon: "error"})</script>';
			}               
		}
	}
	
	public function upload_return_stock_items(){
        $data = array();
        $returnData = array();
        $fetchData = array();
		
		$warehouse_id = $this->input->post('warehouse_id');
		$type = $this->input->post('type');
		
        if (!empty($_FILES['fileURL']['name']))
        {
            // get file extension
            $extension = pathinfo($_FILES['fileURL']['name'], PATHINFO_EXTENSION);
            if ($extension == 'xlsx')
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            else
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
			
            // file path
            $spreadsheet = $reader->load($_FILES['fileURL']['tmp_name']);
            $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
			
            // array Count
            $arrayCount = count($allDataInSheet);
          
            $flag = 0;
			if($type == 'purchase'){
				$createArray = array("product","quantity","amount");
				$makeArray = array("product" => "product","quantity" => "quantity","amount" => "amount");
			} else{
				$createArray = array("order_id", "sku_code", "size", "quantity");
				$makeArray = array("order_id" => "order_id","sku_code" => "sku_code","size" => "size","quantity" => "quantity");
			}
			
            $is_return = 0;
         
            $SheetDataKey = array();
            foreach ($allDataInSheet as $dataInSheet) {
                foreach ($dataInSheet as $key => $value) {
                    if (in_array(trim($value), $createArray)) {
                        $value = preg_replace('/\s+/', '', $value);
                        $SheetDataKey[trim($value)] = $key;
                    } 
                }
            }
			
            $dataDiff = array_diff_key($makeArray, $SheetDataKey);
			
            if (empty($dataDiff)){
                $flag = 1;
            }
            if ($flag == 1) {
                for ($i = 2;$i <= $arrayCount;$i++) {
                    $product = filter_var(trim($allDataInSheet[$i][$SheetDataKey['product']]), FILTER_SANITIZE_STRING);
                    $quantity = filter_var(trim($allDataInSheet[$i][$SheetDataKey['quantity']]), FILTER_SANITIZE_STRING);
					
					if($type == 'purchase'){
						$amount = filter_var(trim($allDataInSheet[$i][$SheetDataKey['amount']]), FILTER_SANITIZE_STRING);
					}
					
					if($type == 'purchase'){
						if ($product != ''){
							$fetchData[] = array(
								'product' => $product,
								'quantity' => $quantity,
								'amount' => $amount,
							);						
						}else{
							$returnData[] = array(
								'product' => $product,
								'quantity' => $quantity,
								'amount' => $amount,
							);
							$is_return = 1;
						}
					} else{
						if ($product != ''){
							$fetchData[] = array(
								'product' => $product,
								'quantity' => $quantity,
							);						
						}else{
							$returnData[] = array(
								'product' => $product,
								'quantity' => $quantity,
							);
							$is_return = 1;
						}
					}
                }
				//echo json_encode($returnData);exit();
                $output = '';
				$count_ = $this->inventory_model->import_retrun_stock_items_excel_insert($fetchData,$warehouse_id,$type);
				//echo json_encode($count_);exit();
				return simple_json_output($count_);
			}
			else {
				echo '<script> Swal.fire({title: "Error!",text: "Please import correct file, did not match excel sheet column!",icon: "error"})</script>';
			}               
		}
	}
	
	public function upload_sales_payment_stock_items(){
        $data = array();
        $returnData = array();
        $fetchData = array();
		
		$warehouse_id = $this->input->post('warehouse_id');
		$type = $this->input->post('type');
		
        if (!empty($_FILES['fileURL']['name']))
        {
            // get file extension
            $extension = pathinfo($_FILES['fileURL']['name'], PATHINFO_EXTENSION);
            if ($extension == 'xlsx')
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            else
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
			
            // file path
            $spreadsheet = $reader->load($_FILES['fileURL']['tmp_name']);
            $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
			
            // array Count
            $arrayCount = count($allDataInSheet);
          
            $flag = 0;
			
			$createArray = array("order_id", "sku_code", "size", "amount");
			$makeArray = array("order_id" => "order_id","sku_code" => "sku_code","size" => "size","amount" => "amount");
			
            $is_return = 0;
            $SheetDataKey = array();
            foreach ($allDataInSheet as $dataInSheet) {
                foreach ($dataInSheet as $key => $value) {
                    if (in_array(trim($value), $createArray)) {
                        $value = preg_replace('/\s+/', '', $value);
                        $SheetDataKey[trim($value)] = $key;
                    } 
                }
            }
			
            $dataDiff = array_diff_key($makeArray, $SheetDataKey);
			
            if (empty($dataDiff)){
                $flag = 1;
            }
            
            if ($flag == 1) {
                for ($i = 2;$i <= $arrayCount;$i++) {
                    $ord_id = filter_var(trim($allDataInSheet[$i][$SheetDataKey['order_id']]), FILTER_SANITIZE_STRING);
                    $product = filter_var(trim($allDataInSheet[$i][$SheetDataKey['sku_code']]), FILTER_SANITIZE_STRING);
                    $size = filter_var(trim($allDataInSheet[$i][$SheetDataKey['size']]), FILTER_SANITIZE_STRING);
                    $amount = filter_var(trim($allDataInSheet[$i][$SheetDataKey['amount']]), FILTER_SANITIZE_STRING);
                    
					if ($product != ''){
						$fetchData[] = array(
							'ord_id' => $ord_id,
							'product' => $product,
							'size' => $size,
							'amount' => $amount,
						);						
					} else {
						$returnData[] = array(
							'ord_id' => $ord_id,
							'product' => $product,
							'size' => $size,
							'amount' => $amount,
						);
						$is_return = 1;
					}
                }
                
				// echo json_encode($fetchData);exit();
                $output = '';
				$count_ = $this->inventory_model->import_sales_payment_items_excel_insert($fetchData, $warehouse_id, $type);
				//echo json_encode($count_);exit();
				return simple_json_output($count_);
			}
			else {
				echo '<script> Swal.fire({title: "Error!",text: "Please import correct file, did not match excel sheet column!",icon: "error"})</script>';
			}               
		}
	}
	
	public function upload_sales_return_stock_items(){
        $data = array();
        $returnData = array();
        $fetchData = array();
		
		$warehouse_id = $this->input->post('warehouse_id');
		$type = $this->input->post('type');
		
        if (!empty($_FILES['fileURL']['name']))
        {
            // get file extension
            $extension = pathinfo($_FILES['fileURL']['name'], PATHINFO_EXTENSION);
            if ($extension == 'xlsx')
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            else
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
			
            // file path
            $spreadsheet = $reader->load($_FILES['fileURL']['tmp_name']);
            $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
			
            // array Count
            $arrayCount = count($allDataInSheet);
          
            $flag = 0;
			
			$createArray = array("order_id", "sku_code", "size", "quantity", "reason");
			$makeArray = array("order_id" => "order_id","sku_code" => "sku_code","size" => "size","quantity" => "quantity", "reason" => "reason");
			
            $is_return = 0;
            $SheetDataKey = array();
            foreach ($allDataInSheet as $dataInSheet) {
                foreach ($dataInSheet as $key => $value) {
                    if (in_array(trim($value), $createArray)) {
                        $value = preg_replace('/\s+/', '', $value);
                        $SheetDataKey[trim($value)] = $key;
                    } 
                }
            }
			
            $dataDiff = array_diff_key($makeArray, $SheetDataKey);
			
            if (empty($dataDiff)){
                $flag = 1;
            }
            
            if ($flag == 1) {
                for ($i = 2;$i <= $arrayCount;$i++) {
                    $ord_id = filter_var(trim($allDataInSheet[$i][$SheetDataKey['order_id']]), FILTER_SANITIZE_STRING);
                    $product = filter_var(trim($allDataInSheet[$i][$SheetDataKey['sku_code']]), FILTER_SANITIZE_STRING);
                    $size = filter_var(trim($allDataInSheet[$i][$SheetDataKey['size']]), FILTER_SANITIZE_STRING);
                    $quantity = filter_var(trim($allDataInSheet[$i][$SheetDataKey['quantity']]), FILTER_SANITIZE_STRING);
                    $reason = filter_var(trim($allDataInSheet[$i][$SheetDataKey['reason']]), FILTER_SANITIZE_STRING);
                    
					if ($product != ''){
						$fetchData[] = array(
							'ord_id' => $ord_id,
							'product' => $product,
							'size' => $size,
							'quantity' => $quantity,
							'reason' => $reason,
						);						
					} else {
						$returnData[] = array(
							'ord_id' => $ord_id,
							'product' => $product,
							'size' => $size,
							'quantity' => $quantity,
							'reason' => $reason,
						);
						$is_return = 1;
					}
                }
                
				// echo json_encode($fetchData);exit();
                $output = '';
				$count_ = $this->inventory_model->import_sales_return_items_excel_insert($fetchData, $warehouse_id, $type);
				//echo json_encode($count_);exit();
				return simple_json_output($count_);
			}
			else {
				echo '<script> Swal.fire({title: "Error!",text: "Please import correct file, did not match excel sheet column!",icon: "error"})</script>';
			}               
		}
	}
	
	public function upload_damage_stock_items(){
        $data = array();
        $returnData = array();
        $fetchData = array();
		
		$warehouse_id = $this->input->post('warehouse_id');
		$type = $this->input->post('type');
		
        if (!empty($_FILES['fileURL']['name']))
        {
            // get file extension
            $extension = pathinfo($_FILES['fileURL']['name'], PATHINFO_EXTENSION);
            if ($extension == 'xlsx')
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            else
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
			
            // file path
            $spreadsheet = $reader->load($_FILES['fileURL']['tmp_name']);
            $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
			
            // array Count
            $arrayCount = count($allDataInSheet);
          
            $flag = 0;
			
			$createArray = array("sku_code", "size", "qty");
			$makeArray = array("sku_code" => "sku_code","size" => "size","qty" => "qty");
			
            $is_return = 0;
            $SheetDataKey = array();
            foreach ($allDataInSheet as $dataInSheet) {
                foreach ($dataInSheet as $key => $value) {
                    if (in_array(trim($value), $createArray)) {
                        $value = preg_replace('/\s+/', '', $value);
                        $SheetDataKey[trim($value)] = $key;
                    } 
                }
            }
			
            $dataDiff = array_diff_key($makeArray, $SheetDataKey);
			
            if (empty($dataDiff)){
                $flag = 1;
            }
            
            if ($flag == 1) {
                for ($i = 2;$i <= $arrayCount;$i++) {
                    $product = filter_var(trim($allDataInSheet[$i][$SheetDataKey['sku_code']]), FILTER_SANITIZE_STRING);
                    $size = filter_var(trim($allDataInSheet[$i][$SheetDataKey['size']]), FILTER_SANITIZE_STRING);
                    $quantity = filter_var(trim($allDataInSheet[$i][$SheetDataKey['qty']]), FILTER_SANITIZE_STRING);
                    
					if ($product != ''){
						$fetchData[] = array(
							'product' => $product,
							'size' => $size,
							'quantity' => $quantity,
						);						
					} else {
						$returnData[] = array(
							'product' => $product,
							'size' => $size,
							'quantity' => $quantity,
						);
						$is_return = 1;
					}
                }
                
				// echo json_encode($fetchData);exit();
                $output = '';
				$count_ = $this->inventory_model->import_damage_stock_items_excel_insert($fetchData, $warehouse_id, $type);
				//echo json_encode($count_);exit();
				return simple_json_output($count_);
			}
			else {
				echo '<script> Swal.fire({title: "Error!",text: "Please import correct file, did not match excel sheet column!",icon: "error"})</script>';
			}               
		}
	}
	
	public function upload_other_sku_items(){
        $data = array();
        $returnData = array();
        $fetchData = array();
		
		
        if (!empty($_FILES['fileURL']['name']))
        {
            // get file extension
            $extension = pathinfo($_FILES['fileURL']['name'], PATHINFO_EXTENSION);
            if ($extension == 'xlsx')
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }
            else
            {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
			
            // file path
            $spreadsheet = $reader->load($_FILES['fileURL']['tmp_name']);
            $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
			
            // array Count
            $arrayCount = count($allDataInSheet);
          
            $flag = 0;
			
			$createArray = array("SHOFTWEAR", "OTHER");
			$makeArray = array("SHOFTWEAR" => "SHOFTWEAR","OTHER" => "OTHER");
			
            $is_return = 0;
            $SheetDataKey = array();
            foreach ($allDataInSheet as $dataInSheet) {
                foreach ($dataInSheet as $key => $value) {
                    if (in_array(trim($value), $createArray)) {
                        $value = preg_replace('/\s+/', '', $value);
                        $SheetDataKey[trim($value)] = $key;
                    } 
                }
            }
			
            $dataDiff = array_diff_key($makeArray, $SheetDataKey);
			
            if (empty($dataDiff)){
                $flag = 1;
            }
            
            if ($flag == 1) {
                for ($i = 2;$i <= $arrayCount;$i++) {
                    $sku = filter_var(trim($allDataInSheet[$i][$SheetDataKey['SHOFTWEAR']]), FILTER_SANITIZE_STRING);
                    $other = filter_var(trim($allDataInSheet[$i][$SheetDataKey['OTHER']]), FILTER_SANITIZE_STRING);
                    
					if ($sku != ''){
						$fetchData[] = array(
							'sku' => $sku,
							'other' => $other,
						);						
					} else {
						$returnData[] = array(
							'sku' => $sku,
							'other' => $other,
						);
						
						$is_return = 1;
					}
                }
                
				// echo json_encode($fetchData);exit();
                $output = '';
				$count_ = $this->inventory_model->import_other_sku_items_excel_insert($fetchData);
				echo json_encode($count_);exit();
				// return simple_json_output($count_);
			}
			else {
				echo '<script> Swal.fire({title: "Error!",text: "Please import correct file, did not match excel sheet column!",icon: "error"})</script>';
			}               
		}
	}

    public function upload_products()
    {
        $data       = array();
        $returnData = array();
        $fetchData  = array();
        
        // If file uploaded
        if (!empty($_FILES['fileURL']['name'])) {
            
            // get file extension
            $extension = pathinfo($_FILES['fileURL']['name'], PATHINFO_EXTENSION);
            
            if ($extension == 'xlsx') {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } else {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }
            // file path
            $spreadsheet    = $reader->load($_FILES['fileURL']['tmp_name']);
            $allDataInSheet = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            
            // array Count
            $arrayCount = count($allDataInSheet);
            
            $flag        = 0;
            $createArray = array(
                "product_name",
                "sku_code",
                "category",
                "group_id",
                "color",
                "size",
                "hsn_code",
                "warehouse",
                "intimation",
                "unit",
                "mrp",
                "costing_price",
                "status",
                "listed_in_amazon",
                "listed_in_snapdeal",
                "listed_in_flipkart",
                "listed_in_jio",
                "listed_in_kidsisland",
            );
            
            $makeArray   = array(
                "product_name" => "product_name",
                "sku_code" => "sku_code",
                "category" => "category",
                "group_id" => "group_id",
                "color" => "color",
                "size" => "size",
                "hsn_code" => "hsn_code",
                "warehouse" => "warehouse",
                "intimation" => "intimation",
                "unit" => "unit",
                "mrp" => "mrp",
                "costing_price" => "costing_price",
                "status" => "status",
                "listed_in_amazon" => "listed_in_amazon",
                "listed_in_snapdeal" => "listed_in_snapdeal",
                "listed_in_flipkart" => "listed_in_flipkart",
                "listed_in_jio" => "listed_in_jio",
                "listed_in_kidsisland" => "listed_in_kidsisland",
            );
            
            $is_return   = 0;
            
            $SheetDataKey = array();
            foreach ($allDataInSheet as $dataInSheet) {
                foreach ($dataInSheet as $key => $value) {
                    if (in_array(trim($value), $createArray)) {
                        $value                      = preg_replace('/\s+/', '', $value);
                        $SheetDataKey[trim($value)] = $key;
                    }
                }
            }
            
            $dataDiff = array_diff_key($makeArray, $SheetDataKey);
            if (empty($dataDiff)) {
                $flag = 1;
            }
            
            if ($flag == 1) {
                for ($i = 2; $i <= $arrayCount; $i++) {
                    
                    // Product Name
                    $name = filter_var(trim($allDataInSheet[$i][$SheetDataKey['product_name']]), FILTER_SANITIZE_STRING);
                    
                    // Product SKU
                    $sku = filter_var(trim($allDataInSheet[$i][$SheetDataKey['sku_code']]), FILTER_SANITIZE_STRING);
                    
                    //Category - 16 | Boys > 3 Pcs Suit
                    $category_id_input = trim($allDataInSheet[$i][$SheetDataKey['category']]);
                    list($category_id) = explode(" | ", $category_id_input);
                    
                    // Group ID
                    $group_id = filter_var(trim($allDataInSheet[$i][$SheetDataKey['group_id']]), FILTER_SANITIZE_STRING);
                    
                    // color - 1 | Beige    
                    $color_id_input = filter_var(trim($allDataInSheet[$i][$SheetDataKey['color']]), FILTER_SANITIZE_STRING);
                    list($color_id) = explode(" | ", $color_id_input);
                    $color_name = explode(" | ", $color_id_input)[1];
                    
                    //size - 1 | 0 - 6 M
                    $size_input = filter_var(trim($allDataInSheet[$i][$SheetDataKey['size']]), FILTER_SANITIZE_STRING);
                    list($size_id) = explode(" | ", $size_input);
                    
                    // HSN
                    $hsn          = filter_var(trim($allDataInSheet[$i][$SheetDataKey['hsn_code']]), FILTER_SANITIZE_STRING);
                    
                    //Warehouse - 1 | 0 - 6 M
                    $warehouse = filter_var(trim($allDataInSheet[$i][$SheetDataKey['warehouse']]), FILTER_SANITIZE_STRING);
                    list($warehouse) = explode(" | ", $warehouse);
                    
                    // Intimation
                    $intimation          = filter_var(trim($allDataInSheet[$i][$SheetDataKey['intimation']]), FILTER_SANITIZE_STRING);
                    
                    // Unit
                    $unit          = filter_var(trim($allDataInSheet[$i][$SheetDataKey['unit']]), FILTER_SANITIZE_STRING);
                    $unit = explode(" | ", $unit)[1];
                    
                    // MRP
                    $mrp           = filter_var(trim($allDataInSheet[$i][$SheetDataKey['mrp']]), FILTER_SANITIZE_STRING);
                    
                    // Costing Price
                    $costing_price           = filter_var(trim($allDataInSheet[$i][$SheetDataKey['costing_price']]), FILTER_SANITIZE_STRING);
                    
                    // Status
                    $status_input = filter_var(trim($allDataInSheet[$i][$SheetDataKey['status']]), FILTER_SANITIZE_STRING);
                    $status       = ($status_input == 'Active') ? 1 : 0;
                    
                    // Listed 1
                    $is_listed1 = filter_var(trim($allDataInSheet[$i][$SheetDataKey['listed_in_amazon']]), FILTER_SANITIZE_STRING);
                    $is_listed1 = ($is_listed1 == 'Yes') ? 1 : 0;
                    // Listed 2
                    $is_listed2 = filter_var(trim($allDataInSheet[$i][$SheetDataKey['listed_in_snapdeal']]), FILTER_SANITIZE_STRING);
                    $is_listed2 = ($is_listed2 == 'Yes') ? 1 : 0;
                    // Listed 3
                    $is_listed3 = filter_var(trim($allDataInSheet[$i][$SheetDataKey['listed_in_flipkart']]), FILTER_SANITIZE_STRING);
                    $is_listed3 = ($is_listed3 == 'Yes') ? 1 : 0;
                    // Listed 4
                    $is_listed4 = filter_var(trim($allDataInSheet[$i][$SheetDataKey['listed_in_jio']]), FILTER_SANITIZE_STRING);
                    $is_listed4 = ($is_listed4 == 'Yes') ? 1 : 0;
                    // Listed 5
                    $is_listed5 = filter_var(trim($allDataInSheet[$i][$SheetDataKey['listed_in_kidsisland']]), FILTER_SANITIZE_STRING);
                    $is_listed5 = ($is_listed5 == 'Yes') ? 1 : 0;
                    
                   // Continue From Here
                    
                    if ($name != '') {
                //         $validity = $this->crud_model->check_excel_product_sku($sku);
                //         if ($validity > 0) {
                            
                //             $returnData[] = array(
                //                 'name'           => $name,
                //                 'is_variation'   => 1,
                //                 'group_id'       => $group_id,
                //                 'color_id'       => $color_id,
                //                 'color_name'     => $color_name,
                //                 'sizes'          => $size_id,
                //                 'categories'     => $category_id,
                //                 'unit'           => $unit,
                //                 'item_code'      => $sku,
                //                 'hsn_code'       => $hsn,
                //                 'type'           => '',
                //                 'min_stock'      => $intimation,
                //                 'intimation'     => $intimation,
                //                 'product_mrp'    => $mrp,
                //                 'costing_price'  => $costing_price,
                //                 'status'         => $status,
                //                 'cartoon_qty'    => 0,
                //                 'listed_1'       => $is_listed1,
                //                 'listed_2'       => $is_listed2,
                //                 'listed_3'       => $is_listed3,
                //                 'listed_4'       => $is_listed4,
                //                 'listed_5'       => $is_listed5,
                //                 'listed_6'       => 1,
                //                 'listed_7'       => 1,
                //                 'added_date'     => date("Y-m-d H:i:s"),
            				// );
                //             $is_return = 1;
				
                //         } else {
                            $fetchData[] = array(
                                'name'           => $name,
                                'is_variation'   => 1,
                                'group_id'       => $group_id,
                                'color_id'       => $color_id,
                                'color_name'     => $color_name,
                                'sizes'          => $size_id,
                                'categories'     => $category_id,
                                'unit'           => $unit,
                                'item_code'      => $sku,
                                'hsn_code'       => $hsn,
                                'type'           => '',
                                'min_stock'      => $intimation,
                                'intimation'     => $intimation,
                                'product_mrp'    => $mrp,
                                'costing_price'  => $costing_price,
                                'status'         => $status,
                                'cartoon_qty'    => 0,
                                'listed_1'       => $is_listed1,
                                'listed_2'       => $is_listed2,
                                'listed_3'       => $is_listed3,
                                'listed_4'       => $is_listed4,
                                'listed_5'       => $is_listed5,
                                'listed_6'       => 1,
                                'listed_7'       => 1,
                                'added_date'     => date("Y-m-d H:i:s"),
            				);
                        // }
                    }
                }
                
                $output = '';
                // echo json_encode($fetchData); exit();
                
                if ($count_ = $this->crud_model->import_products_excel_insert($fetchData)) {
                    
                    $output .= '<div class="row">';
                    $output .= '<div class="col-md-12">    <div class="card"><div class="card-body"> <h6>Following Products Not Added - SKU Already Exist!</h6>';
                    $output .= '<div class="table-responsive">';
                    $output .= '<table id="example" class="table d-report">';
                    $output .= '<thead class="fixedHeader">';
                    $output .= '<tr class="alternateRow"> ';
                    $output .= '<th>Product Title</th>';
                    $output .= '<th>SKU</th>';
                    $output .= '<th>Unit</th>';
                    $output .= '<th>Size</th>';
                    $output .= '<th>MRP</th>';
                    $output .= '<th>Costing Price</th>';
                    $output .= '</tr>';
                    $output .= '</thead>';
                    $output .= '<tbody class="scrollContent">';
                    foreach ($returnData as $row) {
                        $output .= '<tr>';
                        $output .= '<td>' . $row['name'] . '</td>';
                        $output .= '<td>' . $row['item_code'] . '</td>';
                        $output .= '<td>' . $row['unit'] . '</td>';
                        $output .= '<td>' . $row['sizes'] . '</td>';
                        $output .= '<td>' . $row['mrp'] . '</td>';
                        $output .= '<td>' . $row['costing_price'] . '</td>';
                        $output .= '</tr>';
                    }
                    
                    
                    $output .= '</tbody>';
                    $output .= '</table>';
                    $output .= '</div>';
                    $output .= '</div>';
                    $output .= '</div>';
                    $output .= '</div></div>';
                    
                    $output .= '<script>$(document).ready(function() {$("#example").DataTable({"ordering": false,"scrollX": true,"scrollY":"500px","scrollCollapse": true,"fixedHeader": true}); }); Swal.fire({title: "Alert!",text: "Some Products Not Added - Product SKU Already Exist!",icon: "warning"});</script>';
                    if (count($returnData) > 0) {
                        echo $output;
                    } else {
                        echo '<script>Swal.fire({title: "Success!",text: "Products Added Successfully!",icon: "success"}).then(() => {location.reload()});</script>';
                    }
                } else {
                    $output .= '<div class="row">';
                    $output .= '<div class="col-md-12">    <div class="card"><div class="card-body"> <h6>Following Products Not Added - Product SKU Already Exist!</h6>';
                    $output .= '<div class="table-responsive">';
                    $output .= '<table id="example" class="table d-report">';
                    $output .= '<thead class="fixedHeader">';
                    $output .= '<tr class="alternateRow"> ';
                    $output .= '<th>Product Title</th>';
                    $output .= '<th>SKU</th>';
                    $output .= '<th>Unit</th>';
                    $output .= '<th>Size</th>';
                    $output .= '<th>MRP</th>';
                    $output .= '<th>Costing Price</th>';
                    $output .= '</tr>';
                    $output .= '</thead>';
                    $output .= '<tbody class="scrollContent">';
                    foreach ($returnData as $row) {
                        $output .= '<tr>';
                        $output .= '<td>' . $row['name'] . '</td>';
                        $output .= '<td>' . $row['item_code'] . '</td>';
                        $output .= '<td>' . $row['unit'] . '</td>';
                        $output .= '<td>' . $row['sizes'] . '</td>';
                        $output .= '<td>' . $row['mrp'] . '</td>';
                        $output .= '<td>' . $row['costing_price'] . '</td>';
                        $output .= '</tr>';
                    }
                    $output .= '</tbody>';
                    $output .= '</table>';
                    $output .= '</div>';
                    $output .= '</div>';
                    $output .= '</div>';
                    $output .= '</div></div>';
                    
                    $output .= '<script>$(document).ready(function() {$("#example").DataTable({"ordering": false,"scrollX": true,"scrollY":"500px","scrollCollapse": true,"fixedHeader": true});
         Swal.fire({title: "Error!",text: "0 Leads added!",icon: "error"}); })</script>';
                    
                    if (count($returnData) > 0) {
                        echo $output;
                    } else {
                        echo '<script>Swal.fire({title: "Success!",text: "Products Added Successfully!",icon: "success"}).then(() => {location.reload()});</script>';
                    }
                }
            } else {
                echo '<script> Swal.fire({title: "Error!",text: "Please import correct file, did not match excel sheet column!",icon: "error"})</script>';
            }
        }
    }

}

?>
