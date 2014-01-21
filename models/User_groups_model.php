<?php

/**
 * User Groups model
 *
 * Required database fields for ACL:
 *
 * user_id - INT
 * group_id - INT
 */
class User_groups_model extends CI_Model {

	/**
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

}