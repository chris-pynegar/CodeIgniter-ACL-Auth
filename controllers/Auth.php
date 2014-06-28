<?php

class Auth extends CI_Controller {

	/**
	 * Data for the view
	 * 
	 * @var array
	 */
	protected $viewdata = array();
	
	public function __construct() {
		parent::__construct();

		$this->load->library('form_validation');
		$this->load->helper('form');
	}

	public function index() {
		// We must be logged in to view this page
		$this->acl_auth->request();

		$this->load->view('auth/index.php', $this->viewdata);
	}

	public function login() {
		// No need to load this page if we are logged in
		if($this->acl_auth->logged_in()) {
			redirect('auth');
		}

		if($this->input->post()) {
			$this->form_validation->set_rules('username', 'Username', 'required');
			$this->form_validation->set_rules('password', 'Password', 'required');

			if($this->form_validation->run()) {
				// Attempt the login
				$login = $this->acl_auth->login($this->input->post('username'), $this->input->post('password'));

				// Show an error if the login fails
				if(!$login) {
					$this->viewdata['error'] = 'Invalid username/password.';
				}
			}
		}

		$this->load->view('auth/login.php', $this->viewdata);
	}

	public function logout() {
		$this->acl_auth->logout();
	}

}