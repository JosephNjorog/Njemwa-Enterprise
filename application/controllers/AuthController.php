<?php 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require_once(APPPATH."controllers/AppController.php");
class AuthController extends AppController {

	public function __construct() {
		parent::__construct();
		$this->load->helper('file'); 
		$this->licenseControl();
 
	}
	public function login() { 
		
		// if ( $_SERVER['SERVER_NAME'] == "POSlite.herokuapp.com" ) { 
		// 	// If they access the heroku default domain, will redirect the user to our new custom domain

		// 	header("location: https://www.POSlitesoftware.com");

	 // 	}

		if ($this->session->userdata('log_in')) redirect(base_url('dashboard'));
		

		// $this->load->dbutil();
			
		// if (!$this->dbutil->database_exists('POSlite') && !SITE_LIVE) {
		// 	return $this->load->view('buy');
		// }
 	

 	
 
		$this->load->view('login'); 
	}
 

	public function login_validation() {
		$username = $this->input->POSt('username');
		$password = $this->input->POSt('password');
		$this->form_validation->set_rules('username','Username', 'required');
		$this->form_validation->set_rules('password','Password', 'required');
		

		if ($this->form_validation->run() == FALSE) {
			$this->session->set_flashdata('errorMessage','<div class="alert alert-danger">' . validation_errors() . '</div>');
			return redirect(base_url('login'));
		} 

		$this->load->model('UsersModel');

		$verify_login = $this->UsersModel->login($username);
		 
		if ($verify_login) {
			$hash_password = $verify_login->password;

			$verifyPassword = password_verify($password,$hash_password);
			
			if ($verifyPassword) {
				$userdata = array( 
					'id' => $verify_login->id,
					'username' => $verify_login->username,
					'log_in' => true,
					'account_type' => $verify_login->account_type
					);
				$this->db->insert('history', [
						'user_id' => $verify_login->id,
						'action' => 'Log in',
					]);
				$this->session->set_userdata($userdata);
				$this->session->set_flashdata('successMessage','<div class="alert alert-success">Login Successfully, Welcome '.$this->session->userdata['username'].'</div>');
				return $this->redirectUser();
				
			}
			
			$this->session->set_flashdata('errorMessage','<div class="alert alert-danger">Incorrects Login Name Or Password</div>');
			return redirect( 'login' );
			
		} 
		
		$this->session->set_flashdata('errorMessage','<div class="alert alert-danger">Incorrect Login Name Or Password</div>');
		return redirect( 'login' );
		 
		 
	}

	public function redirectUser() {
		if ($this->session->userdata('account_type') == "Cashier")  
			return redirect( 'NE' );
	 

		if ( $this->session->userdata('account_type') == "Admin") 
			return redirect( 'dashboard' );

		if ( $this->session->userdata('account_type') == "Receiver" )
			return redirect( 'deliveries' );
	 
 
	}

	public function logout() {
		$this->inserLoginHistory();
		$data = array('id','username','log_in','account_type');
		
		$this->session->unset_userdata($data);
		$this->session->sess_destroy();
		
 
		$this->session->set_flashdata('successMessage','<div class="alert alert-success">Lagout Successfully</div>');
		redirect(base_url('login'));
	}

	public function inserLoginHistory() {
		$this->db->insert('history', [
							'user_id' => $this->session->userdata('id'),
							'action' => 'Log out',
						]);
	}
}

?>