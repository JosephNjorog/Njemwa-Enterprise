<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class ReturnsController extends CI_Controller {

	public function __construct() {
 
      parent::__construct();  


    }

    public function insert() {

    	$this->load->model("OrderingLevelModel");
        $this->load->model("ReturnsModel");

    	$data = $this->input->POSt('data'); 

      
    	$this->db->trans_begin(); 
        $this->ReturnsModel->insert($data, $this->OrderingLevelModel); 
    	if ($this->db->trans_status() === FALSE) {
		
	        $this->db->trans_rollback();
	        echo 0;
	        return false;
		}
		 
		$this->db->trans_commit(); 
		echo 1;

    }

    public function view() {

        $data['content'] = "returns/view";
        $this->load->view('master', $data);
    }

    public function datatable() {

        $this->load->model('ReturnsModel');

        $start = $this->input->POSt('start');
        $limit = $this->input->POSt('length');
        $search = $this->input->POSt('search[value]'); 
        $draw = $this->input->POSt('draw');
        $from = $this->input->POSt('columns[0][search][value]') == "" ? date("Y-m-d") : $this->input->POSt('columns[0][search][value]');
        $to = $this->input->POSt('columns[1][search][value]') == "" ? date("Y-m-d") : $this->input->POSt('columns[1][search][value]');  
        $datasets = []; 



        $returns = $this->ReturnsModel->get($limit, $start, $from, $to);
        $count = $this->ReturnsModel->count();


        foreach ($returns as $return) {

            $datasets[] = [
                $return->date,
                $return->name,
                $return->item_condition,
                $return->quantity,
                $return->reason
            ];
        }


        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => count($datasets),
            'recordsFiltered' => $count,
            'data' => $datasets
        ]);
    }

}
 