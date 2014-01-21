<?php

/**
 * Users model
 */
class Users_model extends CI_Model {

	/**
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Finds a user by their ID
	 *
	 * @param int $id
	 * @return object
	 */
	public function find_by_id($id) {
		$this->db->where('id', $id);
		return $this->db->get('users')->row();
	}

	/**
	 * Find a user by their username and id
	 *
	 * @param string $username
	 * @param string $password
	 * @return object
	 */
	public function find_user($username, $password) {
		$this->db->where('username', $username);
		$this->db->where('password', $password);
		return $this->db->get('users')->row();
	}

}