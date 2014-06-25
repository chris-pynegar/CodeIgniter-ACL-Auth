<?php

/**
 * ACL Auth library for CodeIgniter
 */
class ACL_Auth {

	/**
	 * @var array
	 */
	protected $config = array();

	/**
	 * @var object
	 */
	private $ci;

	/**
	 * @var bool
	 */
	private $acl_enabled = false;

	/**
	 * @var bool
	 */
	private $logged_in = false;

	/**
	 * @var array
	 */
	private $urls = array();

	/**
	 * @var array
	 */
	private $controllers = array();

	/**
	 * @var object
	 */
	private $current_route;

	/**
	 * @var object
	 */
	public $user;

	/**
	 * @return void
	 */
	public function __construct() {
		// Get the CI object
		$this->ci = &get_instance();

		// Load the ACL Auth config
		$this->ci->load->config('acl_auth', true);
		$this->config = $this->ci->config->config['acl_auth'];

		// Load dependencies
		$this->load_dependencies();

		// Check if the user exists in the session
		if($user_id = $this->ci->session->userdata($this->config['session_keys']['user'])) {
			$user = $this->call_model('users', 'find_by_id', array($user_id));

			// If a user is found then they are logged in
			if($user) {
				$this->logged_in = true;
				$this->user = $user;
			}
		}

		// Set the current route
		$this->current_route();

		// Check to see if ACL is enabled
		if($this->acl_enabled = $this->config['acl_enabled']) {
			// If ACL is enabled check that the user has access to the current url
			if(!$this->has_access()) {
				// Show 404 if access is denied
				show_404();
			}
		}
	}

	/**
	 * Loads the library dependencies
	 *
	 * @return void
	 */
	private function load_dependencies() {
		// Load required helpers
		$this->ci->load->helper('url');

		// Load require models
		$this->ci->load->model(array_values($this->config['models']));
	}

	/**
	 * Call to a model method
	 *
	 * @param string $model
	 * @param string $method
	 * @param array $params
	 * @return mixed
	 */
	private function call_model($model, $method, array $params = array()) {
		// Get correct model name
		$model = $this->config['models'][$model];

		return call_user_func_array(array($this->ci->$model, $method), $params);
	}

	/**
	 * Attempts to log in a user
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function login($username, $password) {
		// Encrypt the password
		$password = $this->encrypt_password($password);

		// Lets try and find the user
		$user = $this->call_model('users', 'find_user', array($username, $password));

		// Did we find a user
		if($user) {
			// Store the id in a session
			$this->ci->session->set_userdata($this->config['session_keys']['user'], $user->id);

			// Record user as logged in
			$this->logged_in = true;
			$this->user = $user;

			// Check to see if we have a last page stored to redirect to
			if($last_page = $this->ci->session->userdata($this->config['session_keys']['last_page'])) {
				// Delete the last page session
				$this->ci->session->unset_userdata($this->config['session_keys']['last_page']);

				// Go to last viewed page
				redirect($last_page);
			}

			// If a login redirect is not null go there
			if($this->config['url']['on_login'] !== null) {
				redirect($this->config['url']['on_login']);
			}

			return true;
		}
		// If not return false;
		else {
			return false;
		}
	}

	/**
	 * Logs a user out
	 *
	 * @return void
	 */
	public function logout() {
		// If user is really logged in, unset the id session
		if($this->logged_in()) {
			$this->ci->session->unset_userdata($this->config['session_keys']['user']);
			$this->logged_in = false;
			$this->user = null;
		}

		// Redirect to the logout url if one is set
		if($this->config['url']['on_logout'] !== null) {
			redirect($this->config['url']['on_logout']);
		}
	}

	/**
	 * Encrypts the users password
	 *
	 * @param string $password
	 * @return string
	 */
	public function encrypt_password($password) {
		// Check that we have a valid algorithm set
		if(!in_array($this->config['encryption_method'], hash_algos())) {
			show_error('ACL Auth: Invalid encryption method set.');
		}
		
		return hash($this->config['encryption_method'], $password.$this->config['salt']);
	}

	/**
	 * Is the user logged in
	 *
	 * @return bool
	 */
	public function logged_in() {
		return $this->logged_in;
	}

	/**
	 * Requests that the user must login to continue
	 *
	 * @return void
	 */
	public function request() {
		// Get the current route
		$route = $this->current_route;
		$current = $route->directory . $route->class . '/' . $route->method;

		if(!$this->logged_in() && $current !== $this->config['url']['to_login']) {
			// Store the current route so we can redirect back to it on successful login
			$this->ci->session->set_userdata($this->config['session_keys']['last_page'], current_url());

			// Redirect user to the login page
			redirect($this->config['url']['to_login']);
		}
	}

	/**
	 * Checks if the user has access to the current route
	 *
	 * @return bool
	 */
	public function has_access() {
		// Do we need to get the allowed CLI URLs?
		$allowed_urls = !is_cli() ? 'allowed_urls' : 'allowed_cli_urls';

		// Get our current class
		$current_class = $this->current_route->directory . $this->current_route->class;

		// Check the publicly allowed urls
		if(array_key_exists($current_class, $this->config[$allowed_urls])) {
			return in_array($this->current_route->method, $this->config[$allowed_urls][$current_class]) || $this->config[$allowed_urls][$current_class] === '*';
		}

		// Check database if user is logged in
		if($this->logged_in()) {
			// Check the database that the user has access
			return $this->call_model('group_access', 'user_has_access', array($current_class, $this->current_route->method, $this->user->id));
		}
		// If we are not logged in, request the login page
		else {
			if(!is_cli()) $this->request();
		}

		// Access denied if no match found
		return false;
	}

	/**
	 * Gets the current route
	 *
	 * @return object
	 */
	private function current_route() {
		// If we have already created the current route object then return it
		if(is_object($this->current_route)) return $this->current_route;

		$route = new stdClass;
		$route->directory = $this->ci->router->directory;
		$route->class = $this->ci->router->class;
		$route->method = $this->ci->router->method;

		return $this->current_route = $route;
	}

	/**
	 * Returns an array of accessible urls
	 *
	 * @return array
	 */
	public function accessible_routes() {
		// Extract our urls if we haven't already
		if(empty($this->urls))
			$this->get_accessible_routes();

		// Sort the urls array
		ksort($this->urls);

		return $this->urls;
	}

	/**
	 * Get accessible public routes
	 *
	 * @return array
	 */
	private function get_accessible_routes() {
		// Scan for our controllers
		$this->get_controller_classes(scandir($this->controller_path()));

		// Extract our controller methods
		foreach($this->controllers as $controller) {
			// We need to explode by '/' in case the controller is in a directory
			$arr = explode('/', $controller);
			
			// Get the cass
			$class = array_pop($arr);

			// Extract the public methods
			$this->extract_methods($class, (string)array_shift($arr));
		}
	}

	/**
	 * £xtract the controller classes
	 *
	 * @param array $controllers
	 * @param string $directory
	 * @return void
	 */
	private function get_controller_classes(array $controllers, $directory = '') {
		// Loop through each controllers and get the public methods
		foreach($controllers as $controller) {
			// Force continue on hidden folders
			if(in_array($controller, array('.', '..'), true)) {
				continue;
			}

			// Ensure controller is a valid php file
			if(strlen($controller) > 4 && strtolower(substr($controller, -4)) == '.php') {
				// Get the class name
				$class = ucfirst(strtolower(substr($controller, 0, -4)));

				// If its in a directory ensure it ends with a '/'
				if($directory !== '' && substr($directory, -1) !== '/') {
					$directory = $directory . '/';
				}

				// Load the class if it has not already been loaded
				if(!$this->class_loaded($class)) {
					include $this->controller_path().$directory.$controller;
                }

                // Push new urls to the array
                array_push($this->controllers, $directory . $class);
			}
			// If its a directory, loop through the directories controllers
			elseif(is_dir($this->controller_path() . $controller) && $directory === '') {
				$this->get_controller_classes(scandir($this->controller_path() . $controller . '/'), $controller);
			}
		}
	}

	/**
	 * Extracts the public methods accessibe by the URL from the controller
	 *
	 * @param object $class
	 * @param string $directory
	 * @return void
	 */
	private function extract_methods($class, $directory = '') {
		// Ensure directory ends with a trailing '/'
		$directory = $this->format_dir_var($directory);

		// Instantiate the reflection class
		$reflector = new ReflectionClass($class);

		foreach($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if(strtolower($method->class == $class) && substr($method->name, 0, 1) != '_') {
                $this->urls[$directory . strtolower($class)][] = $method->name;
            }
        }
	}

	/**
	 * Correctly formats the directory variable
	 *
	 * @param string $directory
	 * @return string
	 */
	private function format_dir_var($dir = '') {
		if($dir !== '' && substr($dir, -1) !== '/') {
			$dir = $dir . '/';
		}

		return $dir;
	}

	/**
	 * Checks if the class is loaded
	 *
	 * @param string $class
	 * @return bool
	 */
	private function class_loaded($class) {
		return in_array($class, get_declared_classes(), true);
	}

	/**
	 * Returns the controller path
	 *
	 * @return string
	 */
	private function controller_path() {
		return '/'.APPPATH.'controllers/';
	}

}