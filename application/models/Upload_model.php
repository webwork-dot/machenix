<?php
defined('BASEPATH') OR exit('No direct script access allowed');
//include image resize library
include APPPATH . "third_party/image-resize/ImageResize.php";
include APPPATH . "third_party/image-resize/ImageResizeException.php";

use \Gumlet\ImageResize;
use \Gumlet\ImageResizeException;

class Upload_model extends CI_Model
{
	//upload temp image
	public function upload_temp_image($file_name)
	{
		if (isset($_FILES[$file_name])) {
			if (empty($_FILES[$file_name]['name'])) {
				return null;
			}
		}
		$config['upload_path'] = './uploads/temp/';
		$config['allowed_types'] = '*';
		$config['file_name'] = 'img_temp_' . generate_unique_id();
		$this->load->library('upload', $config);

		if ($this->upload->do_upload($file_name)) {
			$data = array('upload_data' => $this->upload->data());
			if (isset($data['upload_data']['full_path'])) {
				return $data['upload_data']['full_path'];
			}
			return null;
		} else {
			return null;
		}
	}
	

	 public function video_upload($file_name){  
		  if (isset($_FILES[$file_name])) {
				if (empty($_FILES[$file_name]['name'])) {
					return null;
				}
			}
			
			date_default_timezone_set('Asia/Calcutta'); 
            $year  = date('Y');
            $month = date('m');
            $dates = date('d');
            $config['upload_path']   = '../upload/gallery/' . $year . '/' . $month . '/' . $dates . '/';
            $config['allowed_types'] = '*';
            $this->load->library('upload', $config);
            if ($this->upload->do_upload($file_name)) {
               $idata = $this->upload->data();                
               $url =  'upload/gallery/' . $year . '/' . $month . '/' . $dates . '/' . $idata["file_name"];
			   return $url;
            } else {				
                return NULL;
            }
	}

	public function sign_image_upload($path,$file)
	{
		try {
			$image = new ImageResize($path);
			$image->quality_jpg = 70;
			$image->crop(200, 100, true);
			$new_path = 'uploads/sign/' . $file . '.jpg';
			$image->save(FCPATH . $new_path, IMAGETYPE_JPEG);
			//add watermark
			return $new_path;
		} catch (ImageResizeException $e) {
			return null;
		}
	}

	
	public function image_thumbnail_upload($path, $folder,$file_name)
	{
		try {
			$image = new ImageResize($path);
			$image->quality_jpg = 70;
			$image->resizeToHeight(600);
			$new_path = 'uploads/' . $folder . '/' . $file_name;
			$image->save(FCPATH . $new_path, IMAGETYPE_JPEG);
			return $new_path;
		} catch (ImageResizeException $e) {
			return null;
		}
	}
	
	public function image_user_upload($path, $folder,$file_name)
	{
		try {
			$image = new ImageResize($path);
			$image->quality_jpg = 70;
			$image->resizeToHeight(300);
			$new_path = 'uploads/' . $folder . '/' . $file_name;
			$image->save(FCPATH . $new_path, IMAGETYPE_JPEG);
			return $new_path;
		} catch (ImageResizeException $e) {
			return null;
		}
	}

	public function check_file_mime_type($file_name, $allowed_types)
	{
		if (!isset($_FILES[$file_name])) {
			return false;
		}
		if (empty($_FILES[$file_name]['name'])) {
			return false;
		}
		$ext = pathinfo($_FILES[$file_name]['name'], PATHINFO_EXTENSION);
		if (in_array($ext, $allowed_types)) {
			return true;
		}
		return false;
	}	
	


	//product default image upload
	public function category_image_upload($path, $folder)
	{
		try {
			$image = new ImageResize($path);
			$image->quality_jpg = 70;
			$image->resizeToHeight(60);
			$new_name = generate_unique_id() . '.jpg';
			$new_path = $folder . $new_name;
			$image->save(FCPATH . $new_path, IMAGETYPE_JPEG);	
			return $new_path;
		} catch (ImageResizeException $e) {
			return null;
		}
	}
		//product default image upload
	public function image_upload($path, $folder)
	{
		try {
			$image = new ImageResize($path);
			$image->quality_jpg = 70;
			$image->resizeToHeight(800);
			$new_name = generate_unique_id() . '.jpg';
			$new_path = $folder . $new_name;
			$image->save(FCPATH . $new_path, IMAGETYPE_JPEG);	
			return $new_path;
		} catch (ImageResizeException $e) {
			return null;
		}
	}	
	
	public function img_upload($path, $folder){
		try {
			$image = new ImageResize($path);
			$image->quality_jpg = 70;
			$image->resizeToHeight(500);
			$ext=getExtension($path);
			$new_name = date("Ymdhis").'_'. generate_unique_id() . '.'.$ext;
			$new_path = $folder . $new_name;
			$image->save($new_path);		
			return $new_path;
		} catch (ImageResizeException $e) {
			return null;
		}
	}

	//product default image upload
	public function product_default_image_upload($path, $folder){
		try {
			$image = new ImageResize($path);
			$image->quality_jpg = 70;
			$image->resizeToHeight(900);
			$ext=getExtension($path);
			$new_name = generate_unique_id() . '.'.$ext;
			$new_path = 'uploads/' . $folder . '/' . $new_name;
			$image->save($new_path);

			return $new_name;
		} catch (ImageResizeException $e) {
			return null;
		}
	}
	
	public function voice_upload($path, $folder)
	{
		try {
			$image = new ImageResize($path);
			$image->quality_jpg = 70;
			//$image->resizeToHeight(60);
			$new_name = generate_unique_id() . '.jpg';
			$new_path = $folder . $new_name;
			$image->save(FCPATH . $new_path, IMAGETYPE_JPEG);	
			return $new_path;
		} catch (ImageResizeException $e) {
			return null;
		}
	}
	

	public function delete_temp_voice($path)
	{
		if (file_exists($path)) {
			@unlink($path);
		}
	}
	
	public function new_image_upload($path, $folder)
	{
		try {
			$image = new ImageResize($path);
			$image->quality_jpg = 85;
			$image->resizeToHeight(800);
			$ext=getExtension($path);
			$new_name = generate_unique_id() . '.'.$ext;
			$new_path = $folder . $new_name;
			$image->save($new_path);				
			$final_path =str_replace("../","",$new_path);			
			return $final_path;
		} catch (ImageResizeException $e) {
			return null;
		}
	}
	
	public function flash_image_upload($path, $folder){
		try {
			$image = new ImageResize($path);
			$image->quality_jpg = 90;
			//$image->resizeToHeight(500);
			$ext=getExtension($path);
			$new_name = generate_unique_id() . '.'.$ext;
			$new_path = $folder . $new_name;
			$image->save($new_path);				
			$final_path =str_replace("../","",$new_path);			
			return $final_path;
		} catch (ImageResizeException $e) {
			return null;
		}
	}
	
	//delete temp image
	public function delete_temp_image($path){
		if (file_exists($path)) {
			@unlink($path);
		}
	}

}
