<?php

// Security salt
$config['salt'] = '78563978347571';

// Encryption method e.g. sha1, md5
$config['encryption_method'] = 'sha1';

// ACL enabled
$config['acl_enabled'] = true;

// Allowed URLs for all users
$config['allowed_urls'] = array(
	'account' => array(
		'login'
	)
);

// Allowed CLI URLs
$config['allowed_cli_urls'] = array();

// Session keys
$config['session_keys'] = array(
	// The session that will store the user
	'user' => 'acl_auth_user',
	// The session that stores the last viewed page
	'last_page' => 'acl_auth_last_page'
);

// URLs for redirects
$config['url'] = array(
	// URL to go to when the user logs in
	'on_login' => 'account',
	// URL to go to when user logs out
	'on_logout' => 'account/login',
	// The login page
	'to_login' => 'account/login'
);

// Models for tables
$config['models'] = array(
	// Model for the grop_access table
	'group_access' => 'group_access_model',
	// Model for the groups table
	'groups' => 'groups_model',
	// Model for the user_groups table
	'user_groups' => 'user_groups_model',
	// Model for the users table
	'users' => 'users_model',
);