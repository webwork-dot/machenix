<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Category_model extends CI_Model{
	
	public function getCategories() {
        $this->db->select('id,parent_id,name');
        $this->db->from('categories');  
        return $this->db->get()->result_array();
    }
	
	public function getCategoriesById($parent_id) {
        $this->db->select('id,parent_id,name');
        $this->db->from('categories');  
        $this->db->where('parent_id',$parent_id);  
        return $this->db->get()->result_array();
    }
	
	public function getCategoryBySlug($slug) {
        $this->db->select('id,parent_id,name');
        $this->db->from('categories');  
        $this->db->where('slug',$slug); 
        return $this->db->get()->row_array();
    }

    public function category_list($sortBy='id', $sort='ASC', $limit='', $start='', $keyword=''){

      $this->db->select('*');
      $this->db->from('tbl_category'); 
      $this->db->where('parent_id',0); 
      if($limit!=''){
        $this->db->limit($limit, $start);
      }
      if($keyword!=''){
        $this->db->like('category_name',$keyword);
      }
      
      $this->db->order_by($sortBy,$sort);
      return $this->db->get()->result();
    }

    public function single_category($id){

      $this->db->select('*');
      $this->db->from('tbl_category');
      $this->db->where('id', $id); 
      $this->db->limit(1);
      $query = $this->db->get();
      if($query -> num_rows() == 1){                 
          return $query->result();
      }
      else{
          return false;
      }
    }



    public function insert($data){


       if($this->db->insert('tbl_category',$data))
       {
          return true;
       }
       else
       {
          return false;
       }

   }

   public function update($id,$data){

      $this->db->where('id',$id);
      $result = $this->db->update('tbl_category',$data);

      if($result)
      {
          return true;
      }
      else
      {
          return false;
      }

   }
   
    public function category_delete($id){
      $this->db->select('id,category_image');
      $this->db->from('tbl_category');
      $this->db->where('id', $id); 
      $this->db->limit(1);
      $query = $this->db->get();
      if($query -> num_rows() == 1){   
          $item=$query->row_array();
          $image=$item['category_image'];
          $this->db->where('id', $id);
          $this->db->delete('tbl_category');
          delete_file_from_server('../'.$image);
          return 'success';
      }
      else{
          return 'failed';
      } 
    }
    
   public function delete($id){

      $this->db->select('*');
      $this->db->from('tbl_sub_category');
      $this->db->where('category_id', $id); 
      $query = $this->db->get();
      foreach ($query->result_array() as $result) 
      {
          unlink('assets/images/sub_category/'.$result['sub_category_image']);
          $mask = $result['sub_category_slug'].'*_*';
          array_map('unlink', glob('assets/images/sub_category/thumbs/'.$mask));
      }
      $this->db->select('*');
      $this->db->from('tbl_product');
      $this->db->where('category_id', $id);
      $query = $this->db->get(); 
      foreach ($query->result_array() as $result) 
      {
          if(file_exists('assets/images/products/'.$result['featured_image'])){
            unlink('assets/images/products/'.$result['featured_image']);
            $mask = $result['product_slug'].'*_*';
            array_map('unlink', glob('assets/images/products/thumbs/'.$mask));

          }

          if(file_exists('assets/images/products/'.$result['featured_image2'])){
            unlink('assets/images/products/'.$result['featured_image2']);
            
            $mask = $result['id'].'*_*';
            array_map('unlink', glob('assets/images/products/thumbs/'.$mask));
          }

          if($result['size_chart']!=''){
            unlink('assets/images/products/'.$result['size_chart']);
          }

          $where=array('parent_id' => $result['id'], 'type' => 'product');

          $this->db->select('*');
          $this->db->from('tbl_product_images');
          $this->db->where($where); 
          $query = $this->db->get();
          foreach ($query->result_array() as $result_gallery) 
          {
              unlink('assets/images/products/gallery/'.$result_gallery['image_file']);
          }

          $this->db->delete('tbl_product_images', $where);

          $this->db->select('*');
          $this->db->where('find_in_set("'.$result['id'].'", product_ids) <> 0');
          $this->db->from('tbl_banner');
          $query = $this->db->get();

          foreach ($query->result_array() as $row_banner) 
          {

            $old_ids=explode(',', $row_banner['product_ids']);

            $key = array_search($result['id'], $old_ids);
            if (false !== $key) {
                unset($old_ids[$key]);
            }

            $ids=implode(',', $old_ids);

            $data=array('product_ids' => $ids);

            $this->db->where('id', $row_banner['id']);
            $result_updated = $this->db->update('tbl_banner',$data);

          }

      }

      $this->db->delete('tbl_sub_category', array('category_id' => $id)); 
      $this->db->delete('tbl_product', array('category_id' => $id));
      
      $this->db->select('*');
      $this->db->from('tbl_category');
      $this->db->where('id', $id); 
      $this->db->limit(1);
      $query = $this->db->get();
      if($query -> num_rows() == 1){                 
          $row=$query->result();

          if(file_exists('assets/images/category/'.$row[0]->category_image)){
              unlink('assets/images/category/'.$row[0]->category_image);

              $mask = $row[0]->category_slug.'*_*';
              array_map('unlink', glob('assets/images/category/thumbs/'.$mask));
              
          }  
          $this->db->where('id', $id);
          $this->db->delete('tbl_category');
          return 'success';
      }
      else{
          return 'failed';
      }
      
   }



	

     public function get_category_list_count($filter_data)  {   
        $keyword_filter = "";

        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $keyword_filter = " AND (category_name like '%" . $keyword . "%')";
        endif;
        
        $query = $this->db->query("SELECT id FROM tbl_category  WHERE parent_id='0' $keyword_filter ORDER BY id DESC");
        return $query->num_rows();
    }
    
    
    public function get_category_list($filter_data, $per_page, $offset){
        $resultdata = array();
        $keyword_filter = "";
        
        if (isset($filter_data['keywords']) && $filter_data['keywords'] != ""):
            $keyword        = $filter_data['keywords'];
            $keyword_filter = " AND (category_name like '%" . $keyword . "%')";
        endif;
                
        $query = $this->db->query("SELECT * FROM tbl_category WHERE parent_id='0' $keyword_filter ORDER BY id DESC LIMIT $offset,$per_page");
        return $query->result();
    }
    
    public function buildTree($categories, $parent_id = 0) {
        $tree = array();

        foreach ($categories as $category) {
            if ($category['parent_id'] == $parent_id) {
                $children = $this->buildTree($categories, $category['id']);
                if ($children) {
                    $category['children'] = $children;
                }
                $tree[] = $category;
            }
        }

        return $tree;
    }
    
    public function build_query($all_columns = false){
        if ($all_columns == true) {
            $this->db->select('categories.*, categories.parent_id AS join_parent_id');
        } else {
            $this->db->select('categories.id, categories.slug, categories.parent_id, categories.parent_tree, categories.parent_id AS join_parent_id');
        }
     
        $this->db->select('(SELECT slug FROM categories WHERE id = join_parent_id) AS parent_slug');
        $this->db->select('(SELECT id FROM categories AS sub_category WHERE sub_category.parent_id = categories.id LIMIT 1) AS has_subcategory');
    }
    
    public function order_by_categories($result_type = null)    {
        $sort = '';
        if ($sort == "date") {
            $this->db->order_by('categories.created_at');
        } elseif ($sort == "date_desc") {
            $this->db->order_by('categories.created_at', 'DESC');
        } elseif ($sort == "alphabetically") {
            $this->db->order_by('name');
        } else {
            $this->db->order_by('id','DESC');
        }
    }
    
    public function get_all_parent_categories_by_lang() {
        $this->build_query(true);
        $this->db->where('parent_id', 0);
        $this->order_by_categories();
        return $this->db->get('categories')->result();
    }
    
    public function get_subcategories_by_parent_id($parent_id, $lang_id = null) {
        $this->build_query(true);
        $this->db->where('categories.parent_id', $parent_id);
        $this->order_by_categories();
        $query = $this->db->get('categories');
        return $query->result();
    }
    
    function categoryTree($parent_id = "0", $sub_mark = ""){  
        $data=array();   
        $separator='';
        $query = $this->db->query("SELECT * FROM categories WHERE parent_id = '$parent_id' ORDER BY name ASC");
        if($query->num_rows() > 0){
            foreach($query->result_array() as $row){
            $separator=$sub_mark.'---';
            $data[]= array(
                'id'=>$row['id'],
                'parent_id'=>$row['parent_id'],
                'name'=>$row['name'],
                'separator'=> $separator,
                'separator_space'=> str_replace("-","&nbsp;",$separator),
                'nested_categories'=>$this->categoryTree($row['id'])
                );
            }
        }
        return $data;
    }
    
    public function get_all_parent_categories(){
		$this->db->select('id,parent_id,name');
		$this->db->where('parent_id', 0);
		$this->db->order_by('name');
		$query = $this->db->get('categories');
		return $query->result();
	}
	
    public function get_category_lists($id)	{
		$this->db->where('id', $id);
		$query = $this->db->get('categories');
		return $query->row();
	}
	
		
	public function get_parent_categories_array_by_category_id($category_id) {
		$array_categories = array();
		$category = $this->get_category_lists($category_id);
		if (!empty($category)) {
			array_push($array_categories, $category);
			for ($i = 0; $i < 50; $i++) {
				$parent = $this->get_category_lists($category->parent_id);
				if (!empty($parent)) {
					array_push($array_categories, $parent);
					$category = $parent;
					if ($category->parent_id == 0) {
						break;
					}
				}
			}
		}
		return array_reverse($array_categories);
	}
	
    public function get_categories_all(){
		$this->db->order_by('name', 'asc');
		$query = $this->db->get('categories');
		return $query->result_array();
	}
	
    public function get_categories_json(){
		$categories = $this->get_categories_all();
		$array = array();
		if (!empty($categories)) {
			foreach ($categories as $category) {
				$item = array(
					'id' => $category['id'],
					'parent_id' => $category['parent_id'],
					'name' => $category['name'],
				);
				array_push($array, $item);
			}
		}
		echo json_encode($array);
	}
	
    public function get_category_by_id($id)  {
        $this->db->where('id', $id);
        $query = $this->db->get('categories');
        return $query->row();
    }
    
}