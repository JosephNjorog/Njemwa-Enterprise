<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

 
class PurchaseOrderController extends CI_Controller {

	public function index() {
        $data['page'] = "Purchase Order"; 
		$data['content'] = "purchase/index";
		$this->load->view('master',$data);
    }

    public function new() {
        $supplier_id = $this->input->get('supplier_id');
        
        $itemsToReOrder = $this->db->select('items.*, ordering_level.quantity')
                                    ->from('items')
                                    ->join('ordering_level', 'ordering_level.item_id = items.id')
                                    ->where('items.supplier_id', $supplier_id)
                                    ->where('items.reorderingLevel > ordering_level.quantity')
                                    ->get()
                                    ->result();

        $data['page'] = "Purchase Order"; 
		$data['content'] = "purchase/new";
        $data['itemsToReOrder'] = $itemsToReOrder;
        $data['suppliers'] = $this->db->get('supplier')->result();
        $data['supplier_id'] = $supplier_id;
		$this->load->view('master',$data);
    }

    public function store() {  
        $max_id = $this->db->select_max('id', 'max_id')->get('purchase')->row();
        $poNumber = "PO" . sprintf('%08d', (int)$max_id->max_id + 1);
        $purchase = array(
            'supplier_id' => $this->input->POSt('supplier_id'),
            'status' => $this->input->POSt('status'),
            'eta' => $this->input->POSt('eta'),
            'po_number' => $poNumber
        );

        $this->db->insert('purchase', $purchase);
        $purchase_id = $this->db->insert_id();
        $product_id = $this->input->POSt('product_id');
        $price = $this->input->POSt('price');
        $quantity = $this->input->POSt('quantity');
        $remarks = $this->input->POSt('remarks');  
        $this->storePurchaseLineItems($purchase_id, $product_id, $price, $quantity, $remarks);
        redirect('/purchase');
    }

    private function storePurchaseLineItems($purchase_id, $product_id, $price, $quantity, $remarks) {
       
        $purchaseLineItems = [];
        foreach( $product_id as $key => $id ) {
            $purchaseLineItems[] = array(
                'item_id' => $id,
                'price' => $price[$key],
                'quantity' => $quantity[$key],
                'remarks' => $remarks[$key],
                'purchase_id' => $purchase_id,
                'received' => 0
            );
        }  
        $this->db->insert_batch('purchase_order_line_item', $purchaseLineItems);
    }

    public function datatable() { 
        $start = $this->input->POSt('start');
		$limit = $this->input->POSt('length');
		$search = $this->input->POSt('search[value]'); 
        $draw = $this->input->POSt('draw');
        $status = $this->input->POSt('columns[0][search][value]');
        $count = $this->dataTableQuery($status)->num_rows();
        $result = $this->dataTableQuery($status)->result();
        $dataset = [];
        $badges = [
            'Pending' => 'warning',
            'Open Order' => 'info', 
            'Completed' => 'success'
        ]; 

        foreach ($result as $row) { 
            $badgeClass = $badges[$row->status];
            $editable = $row->status !== "Completed" ? '<li>
                <a href="'. base_url("PurchaseOrderController/edit/" . $row->id) .'"><i class="fa fa-edit"></i> Edit</a> 
            </li>' : null;
            $dataset[] = [
                $row->po_number,
                substr($row->created_date, 0, 10),
                $row->supplier_name,
                $row->eta,
                $row->total,
                "<span class='badge badge-$badgeClass'>$row->status</span>",
                '
				<div class="dropdown">
                    <a href="#" data-toggle="dropdown" class="dropdown-toggle btn btn-primary btn-sm">Actions <b class="caret"></b></a>
                    <ul class="dropdown-menu"> 
                        <li>
                            <a class="purchase-order-view" target="__blank" href="'. base_url("PurchaseOrderController/print/" . $row->id) .'"><i class="fa fa-eye"></i> View</a> 
                        </li> 
                        '.$editable.' 
                    </ul>
                </div> 
				'
            ];
        }

        echo json_encode([
			'draw' => $draw,
			'recordsTotal' => $count,
			'recordsFiltered' => $count,
			'data' => $dataset
		]);
    }


    public function dataTableQuery($status) { 
        return $this->db->select('purchase.*, SUM(purchase_order_line_item.quantity * purchase_order_line_item.price) as total, supplier.name as supplier_name')
                    ->from('purchase')
                    ->join('purchase_order_line_item', 'purchase_order_line_item.purchase_id = purchase.id', 'BOTH')
                    ->join('supplier', 'supplier.id = purchase.supplier_id')
                    ->like('purchase.status', $status)
                    ->group_by('purchase.id', 'DESC')
                    ->order_by('id', 'DESC')
                    ->get();
     
    }

    public function edit($id) {
        $purchase = $this->getPurchaseById($id); 
        $data['page'] = "Purchase Order"; 
		$data['content'] = "purchase/edit"; 
        $data['suppliers'] = $this->db->get('supplier')->result();
        $data['purchase'] = $purchase['data'];
        $data['purchase_line_item'] = $purchase['line_item'];
        $data['supplier_id'] = $id;  
		$this->load->view('master',$data);
    }

    private function getPurchaseById($id) {
        return array(
            'data' => $this->db->where('id', $id)->get('purchase')->row(),
            'line_item' => $this->getPurchaseLineItems($id)
        );
    }

    private function getPurchaseLineItems($id) {
        return $this->db->select('purchase_order_line_item.*, items.name as item_name')
                        ->from('purchase_order_line_item')
                        ->join('items', 'items.id = purchase_order_line_item.item_id')
                        ->where('purchase_id', $id)
                        ->get()
                        ->result();
    }

    public function update() {  
        $purchase_id = $this->input->POSt('purchase_id');
        $product_id = $this->input->POSt('product_id');
        $price = $this->input->POSt('price');
        $quantity = $this->input->POSt('quantity');
        $remarks = $this->input->POSt('remarks'); 
        $received = $this->input->POSt('received'); 
        $status = $this->input->POSt('status'); 
        $purchase = array(
            'eta' => $this->input->POSt('eta'),
            'supplier_id' => $this->input->POSt('supplier_id'),
            'status' => $this->input->POSt('status'), 
        ); 
        $this->db->where('id', $purchase_id)->update('purchase', $purchase); 
        $this->db->where('purchase_id', $purchase_id)->delete('purchase_order_line_item');
        $this->storePurchaseLineItems($purchase_id, $product_id, $price, $quantity, $remarks);

        if ($status === "Completed") {
            $this->load->model('OrderingLevelModel');
            foreach ($product_id as $index => $id) { 
                $this->OrderingLevelModel->addStocks($id, $received[$index]);
            }
        }
        return redirect('purchase');
    }

    public function print($id) {

		$purchase = $this->getPurchaseById($id);  
		$data['purchase']	= $purchase['data'];
		$data['orderline'] = $purchase['line_item'];   
		$this->load->view('receipt/purchase_order', $data);
	}
}