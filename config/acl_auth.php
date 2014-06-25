<?php

/**
 * The security salt to add to your passwords, 
 * this string should contain numeric characters only and is required.
 */
$config['salt'] = '';

/**
 * The algorithm to use when encrypting your passwords.
 */
$config['encryption_method'] = 'sha256';

/**
 * Wether or not you are using full ACL functionality or standard auth.
 */
$config['acl_enabled'] = false;

/**
 * URLs that can be accessed without logged in, the array for this 
 * format must be formatted like this:
 *
 * 'controller1' => array('method1', 'method2'),
 * 'controller2' => array('method2')
 */
$config['allowed_urls'] = array(
	'account' => array(
		'login'
	)
);

/**
 * URLs that can bypass the login redirect when accessed through the
 * Command Line Interface (CLI) and are formatted the same way as the 
 * allowed_urls option.
 */
$config['allowed_cli_urls'] = array();

/**
 * The names of the session keys that will be used by the ACL Auth library.
 */
$config['session_keys'] = array(
	/**
	 * This session stores the logged in user details.
	 */
	'user' => 'acl_auth_user',
	/**
	 * This session stores the page that forwarded us to the login,
	 * it is important for us to remember this so we can return the
	 * user back here on a successful login.
	 */
	'last_page' => 'acl_auth_last_page'
);

/**
 * These are the urls to go to during certain events of the Authentication process.
 */
$config['url'] = array(
	/**
	 * When the user successfully logs in, if we don't have a last 
	 * page stored we must send them to this default page.
	 */
	'on_login' => 'account',
	/**
	 * This is the page the user will be redirected to when they have logged out.
	 */
	'on_logout' => 'account/login',
	/**
	 * This is the login page, should the user attempt to access a restricted
	 * page they will be asked to log in before they can proceed any further.
	 */
	'to_login' => 'account/login'
);

/**
 * These are the names of the models that each table will use, we offer this 
 * configuration to give you more flexibilty when implementing ACL Auth 
 * into existing applications.
 */
$config['models'] = array(
	/**
	 * The model used for handling the routes a group can access.
	 */
	'group_access' => 'group_access_model',
	/**
	 * The model used for handling the groups.
	 */
	'groups' => 'groups_model',
	/**
	 * The model used for handling the user groups.
	 */
	'user_groups' => 'user_groups_model',
	/**
	 * The model used for handling the users
	 */
	'users' => 'users_model',
);