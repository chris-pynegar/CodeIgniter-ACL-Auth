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

	public function login() {
		if($this->input->post()) {
			$this->form_validation->set_rules('username', 'Username', 'required');
			$this->form_validation->set_rules('password', 'Password', 'required');

			if($this->form_validation->run()) {
				// Attempt the login
				$login = $this->acl_auth->login($this->input->post('username'), $this->input->post('password'));

				// Show an error if the login fails
				if(!$login) {

				}
			}
		}

		$this->load->view('auth/login.php', $this->viewdata);
	}

	public function logout() {
		$this->acl_auth->logout();
	}

}