<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH."controllers/AppController.php");
class CategoriesController Extends AppController {

	public function __construct() {
		parent::__construct();
	
		if (!$this->session->userdata('log_in')) {
			$this->session->set_flashdata('errorMessage','<div class="alert alert-danger">Login Is Required</div>');
			redirect(base_url('login'));
		}
	}

	public function categories() { 
		$data['categoryList'] = $this->db->order_by('id','DESC')
									->where('active',1)
									->get('categories')
									->result();
		$data['page'] = 'categories';
		$data['content'] = "categories/index";
		$this->load->view('master',$data);
		 
	}

	public function get() {
		$categories = $this->db->where('active', 1)->get('categories')->result();
		echo json_encode($categories);
	}

	public function insert() {
		$category = $this->input->POSt('category');
		$this->form_validation->set_rules('category', 'Category', 'required');

		if ($this->form_validation->run()) {

			$this->load->model('HistoryModel');
			$this->HistoryModel->insert('Register Category: '. $category);
		 	$this->db->insert('categories', [
		 			'name' => $this->security->xss_clean($category),
		 			'active' => 1
		 		]);
		 	$this->session->set_flashdata('success','Category has been added successfully');
		 	return redirect(base_url('categories'));
		
		} 	
		 
	}

	public function destroy($id) {
		if(empty($id))  
			return redirect(base_url('categories'));
		
		$id = $this->security->xss_clean($id);
		$this->load->model('categories_model');
		$this->load->model('HistoryModel');
		$categoryName = $this->categories_model->getName($id);
		$this->db->where('id', $id)->delete('categories');
 
	 	if ($this->db->error()['code'] === 1451) {
	 		$this->session->set_flashdata('error', 'Cannot Delete Category Associated with Item');
			return redirect(base_url('categories')); 
	 	}
	 
	 	$this->HistoryModel->insert('Deleted Category: ' . $categoryName);
			$this->session->set_flashdata('success','Category deleted successfully');
			return redirect(base_url('categories'));
		
		 
	}

	public function edit($id) {
		$cat = $this->db->where('id', $id)->get('categories')->row();

		$data['content'] = "categories/edit";
		$data['cat'] = $cat;

		$this->load->view('master', $data);
	}

	public function update() {

		$id = $this->input->POSt('id');
		$name = $this->input->POSt('name');

		$this->db->where('id', $id)->update('categories', ['name' => $name]);

		success("Updated Successfully");

		return redirect('categories');
	}
}
?>