<?php

/**
 * Group Access model
 *
 * Required database fields for ACL:
 * 
 * group_id - INT
 * controller - VARCHAR
 * method - VARCHAR
 */
class Group_access_model extends CI_Model {

	/**
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Gets groups with a specific controller and method
	 *
	 * @param string $controller
	 * @param string $method
	 * @param int $user_id
	 * @return bool
	 */
	public function user_has_access($controller, $method, $user_id) {
		$this->db->select('group_access.controller, group_access.method');
		$this->db->select('groups.id');
		$this->db->select('user_groups.user_id');
		$this->db->where('group_access.controller', $controller);
		$this->db->where('(group_access.method = '.$this->db->escape($method).' OR group_access.method = '.$this->db->escape('*').')');
		$this->db->where('user_groups.user_id', $user_id);
		$this->db->from('group_access');
		$this->db->join('groups', 'groups.id = group_access.group_id');
		$this->db->join('user_groups', 'user_groups.group_id = groups.id');
		
		return $this->db->count_all_results() > 0;
	}

}