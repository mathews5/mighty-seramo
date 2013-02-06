<?php
/*
 * Plugin Name: Seramo by mightystudios
 * URI: http://mightystudios.net
 * Description: Send JSON requests to WordPress and get a response!
 * Version: 1.0
 * Author: mightystudios.net
 */

/**
 * Action to initialize the plugin
 */
add_action ( 'init', 'mighty_seramo_init' );


function mighty_seramo_init() {
	error_reporting ( E_ALL );
	ini_set ( "display_errors", 1 );
	
	// include public functions file
	require_once( 'functions.php' );
	
	new mighty_seramo ();
}


class mighty_seramo {
	
	private static $callbacks = array();

	private $new_slug = null;
	
	private static $path;
	
	private static $url;
	
	const WP_OPTION_SERAMO_SLUG		= 'mighty_seramo_slug';
	
	const DEFAULT_SLUG				= 'seramo';
	
	const BASE_PARAM				= 'mightystudios';
	
	function __construct() {
		
		$this->path							=	plugin_dir_path ( __FILE__ );
		
		$this->url 							=	plugins_url( basename(dirname(__FILE__)) . '/' );
		
		/*
		 * get stored slug
		 */
		$MIGHTY_SERAMO_SLUG = get_option ( self::WP_OPTION_SERAMO_SLUG );
		
		if ($this->is_nothing ( $MIGHTY_SERAMO_SLUG )) {
			define ( 'MIGHTY_SERAMO_SLUG', self::DEFAULT_SLUG );
			update_option ( self::WP_OPTION_SERAMO_SLUG, MIGHTY_SERAMO_SLUG );
		} else {
			
			$MIGHTY_SERAMO_SLUG_CLEAN = sanitize_title ( $MIGHTY_SERAMO_SLUG );
			
			/*
			 * check if the slug wasnt clean, if it wasnt update it
			 */
			if ($MIGHTY_SERAMO_SLUG_CLEAN != $MIGHTY_SERAMO_SLUG) {
				$MIGHTY_SERAMO_SLUG = $MIGHTY_SERAMO_SLUG_CLEAN;
				update_option ( self::WP_OPTION_SERAMO_SLUG, $MIGHTY_SERAMO_SLUG );
			}
			
			define ( 'MIGHTY_SERAMO_SLUG', $MIGHTY_SERAMO_SLUG );
		}
		
		// load the admin site of the plugin
		if ( is_admin() ){
			$this->admin_only();
		}
		
		add_filter ( 'query_vars', array (&$this, 'update_query_vars' ) );
		$this->setup_json();
	}
	
	
	
	
	static function install() {
		// Add the rewrite rule on activation
		global $wp_rewrite;
		add_option ( self::MS_POST_OPT_CUSTOMFIELDS, self::DEFAULT_SLUG, '', 'no' );
		add_filter ( 'rewrite_rules_array', array (&$this, 'json_api_rewrites' ) );
		$wp_rewrite->flush_rules ();
	}
	
	
	
	
	static function uninstall() {
		// Remove the rewrite rule on deactivation
		global $wp_rewrite;
		$wp_rewrite->flush_rules ();
	}
	
	
	
	
	private function admin_only(){
		
		add_action ( 'admin_menu', array ( &$this, 'register_plugin_menu') );
		
		add_action( 'wp_ajax_mighty_seramo_save_slug', array(&$this, 'form_save_slug') );
		add_action( 'wp_ajax_mighty_seramo_add_query', array(&$this, 'form_add_query') );
	}
	
	
	
	
	public function register_plugin_menu() {
		add_options_page ( 'Seramo', 'Seramo', 'manage_options', 'mighty-seramo-options', array ($this,'page_seramo_options') );
	}
	
	
	
	
	function page_seramo_options(){
		
		// load in the ui core and effects core
		wp_enqueue_script("jquery-ui-core");
		wp_enqueue_script("jquery-effects-core");
		wp_enqueue_script("jquery-ui-button");
		wp_enqueue_script("jquery-ui-tabs");
		
		// load a jquery ui style from google
		wp_register_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/redmond/jquery-ui.css');
		wp_enqueue_style('jquery-ui-css');
		
		
		// load admin script
		wp_enqueue_script('mighty-seramo-admin', $this->url . 'mighty.seramo.admin.js');
		wp_enqueue_script('mighty-seramo-admin');
		
		// load a jquery ui style from google
		wp_enqueue_style('mighty-seramo-admin', $this->url . 'mighty.seramo.admin.css' );
		//wp_enqueue_style('mighty-seramo-admin');
		
		$this->load_page ( 'options' );
	}
	
	
	
	
	private function load_page($page) {
		$page_file_path = $this->path . 'pages' . DIRECTORY_SEPARATOR . $page . '.php';
		if (file_exists ( $page_file_path )) {
			echo '<div class="wrap"><div class="mighty-page">';
			include_once $page_file_path;
			echo '</div></div>';
		} else {
			echo '<h1>Error, Page Cannot Be Found</h1>';
			echo $page_file_path;
		}
	}
	
	
	
	
	
	/**
	 *  Ajax handler for wp_ajax_mighty_seramo_save_slug
	 *	saves the new slug
	 *
	 *
	 * @return (none)
	 *
	 */
	function form_save_slug(){
		
		$new_json_slug = sanitize_title( $_POST['new_slug'] );
		$new_slug = wp_unique_post_slug($new_json_slug, 0 , 'publish', 'page', 0);
		$this->set_new_slug( $new_slug );
		return exit ( $new_slug );
	}
	
	
	
	
	
	/**
	 *  Ajax handler for wp_ajax_mighty_seramo_add_query
	 *	loads the add-query page
	 *
	 *
	 * @return (none)
	 *
	 */
	function form_add_query(){
		return exit( $this->load_page('add-query') );
	}
	
	/**
	 * Sets the new slug in place for the json call
	 *
	 *
	 *
	 * @return (string) field string
	 *
	 */
	private function set_new_slug( $new_slug ){
				
		$curent_slug_opt = get_option( self::WP_OPTION_SERAMO_SLUG );
		
		if($new_slug != $curent_slug_opt){
			update_option(self::WP_OPTION_SERAMO_SLUG, $new_slug);
			$this->update_rules();			
		}

	}
	
	private function setup_json() {
		
		global $wp_rewrite;
		add_filter ( 'rewrite_rules_array', array (&$this, 'json_api_rewrites' ) );
		$wp_rewrite->flush_rules ();
		
		add_action ( 'template_redirect', array (&$this, 'template_redirect' ) );
		
		add_action ( 'update_option_json_api_base', array (&$this, 'flush_rewrite_rules' ) );
	}
	
	
	
	
	/**
	 * Redirects the template, to load the json and not a page!
	 *
	 * TODO:: better description
	 *
	 * @return (string) field string
	 *        
	 */
	function template_redirect() {
		
		if ($this->has_json_slug ()) {
			
			$slug_val = $this->get_json_slug ();
			
			if($slug_val == self::BASE_PARAM){
				return exit ( json_encode ( array('code' => 1, 'error'=>'No query target was found') ) );
			}
			
			if( isset( self::$callbacks[ $slug_val ] ) ){
				$cb_function = self::$callbacks[ $slug_val ] ['callback_function'];
				
				$cb_expected = self::$callbacks[ $slug_val ]['expected_parameters'];

				$cb_expected_notfound = false;
				
				$cb_expected_notfound_items = array();
				
				// loop and check if all expected parameters are present
				foreach( $cb_expected as $expected_param ){
					
					if( isset($_POST[ $expected_param ]) ){
						$expected_param_val = $_POST[ $expected_param ];
					}else{
						$cb_expected_notfound = true;
						$cb_expected_notfound_items[] = $expected_param;
					}
					
				}
				
				if( $cb_expected_notfound ){
					return exit ( json_encode ( array('code' => 2, 'error'=>'The following expected parameters where not found ('. implode(',',$cb_expected_notfound_items).')' ) ) );
				}
				
				if( function_exists ( $cb_function ) ){
					$results = call_user_func( $cb_function );
				}
				
			}
			return exit ( json_encode ( $results ) );
		}
	
	}
	
	
	/**
	 * Runs the url rewrite flush and update
	 *
	 *
	 *
	 * @return (string) field string
	 *
	 */
	private function update_rules(){
	
		add_filter('rewrite_rules_array', array(&$this, 'json_api_rewrites') );
		add_action('update_option_json_api_base', array(&$this, 'flush_rewrite_rules'));
		$this->flush_rewrite_rules();
	}
	
	
	function flush_rewrite_rules() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules ();
	}
	
	
	
	
	public function has_json_slug() {
		$slug = get_query_var ( MIGHTY_SERAMO_SLUG );
		if ($slug != '') {
			return true;
		} else {
			return false;
		}
	}
	
	
	
	
	public function get_json_slug() {
		$slug = get_query_var ( MIGHTY_SERAMO_SLUG );
		if ($slug != '') {
			return $slug;
		} else {
			return null;
		}
	}
	
	
	
	
	/**
	 * Puts the custom seramo slug into the query vars
	 *
	 * TODO:: better description
	 *
	 * @return (string) field string
	 *        
	 */
	function update_query_vars( $wp_vars ) {
		$wp_vars [] = MIGHTY_SERAMO_SLUG;
		return $wp_vars;
	}
	
	
	
	
	/**
	 * Update the rewrite rules, to catch the json slug
	 *
	 * TODO:: better description
	 *
	 * @return (string) field string
	 *        
	 */
	function json_api_rewrites($wp_rules) {
		
		$base = MIGHTY_SERAMO_SLUG;
		
		$json_api_rules = array ("$base\$" => 'index.php?' . $base . '='. self::BASE_PARAM , "$base/(.+)\$" => 'index.php?' . $base . '=$matches[1]' );
		
		return array_merge ( $json_api_rules, $wp_rules );
	}
	
	
	
	
	/**
	 * Is the value nothing (equal to: null, "", false, 0, "null")
	 *
	 * @param
	 *       	 (mixed) parameter which needs checking
	 *       	
	 * @return (boolean) returns true/false if values is nothing
	 *        
	 */
	private function is_nothing($value) {
		if ($value === null) {
			return true;
		}
		if ($value === "") {
			return true;
		}
		if ($value === false) {
			return true;
		}
		if ($value === 0) {
			return true;
		}
		if (is_string ( $value )) {
			if (strtolower ( $value ) === "null") {
				return true;
			}
		}
		return false;
	}
	
	
	
	
	/**
	 * Is the value false (equal to: "off", "0", 0, false)
	 *
	 * @param
	 *       	 (mixed) parameter which needs checking
	 * @param
	 *       	 (string) parameter which needs checking
	 *       	
	 * @return (boolean) returns true/false if values is true
	 *        
	 */
	function is_false($value, $returnType = "bool") {
		if ($value === 'off' || $value === '0' || $value === 0 || $value === false || $value === "false") {
			$value = 1;
		} else {
			$value = 0;
		}
		
		switch ($returnType) {
			case "bool" :
				if ($value == 1) {
					$value = true;
				} else {
					$value = false;
				}
				break;
			
			case "int" :
				if ($value == 1) {
					$value = 1;
				} else {
					$value = 0;
				}
				break;
			
			case "string" :
				if ($value == 1) {
					$value = "1";
				} else {
					$value = "0";
				}
				break;
			
			case "text" :
				if ($value == 1) {
					$value = "true";
				} else {
					$value = "false";
				}
				break;
			
			default :
				if ($value == 1) {
					$value = true;
				} else {
					$value = false;
				}
				break;
		}
		
		return $value;
	}
	

	function sanatise_string($string){
		$string = str_replace(" ","-", trim($string));
		$string = preg_replace("/[^a-zA-Z0-9-]/","", $string);
		$string = strtolower($string);
		return $string;
	}
	
	function current_page_url() {
		$pageURL = 'http';
		if( isset($_SERVER["HTTPS"]) ) {
			if ($_SERVER["HTTPS"] == "on") {
				$pageURL .= "s";
			}
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
	
	public static function register_callback($slug, $callback, $expected_parameters = array() , $settings = array(), $from){
		
		// check where this function is being registed from
		if($from != 'function' && $from != 'user'){
			return false;
		}
		
		$cb_slug = str_replace(" ","-", trim($slug));
		$cb_slug = preg_replace("/[^a-zA-Z0-9-]/","", $cb_slug);
		$cb_slug = strtolower($cb_slug);
		
		if(!is_array($expected_parameters)){
			$cb_expected_parameters = array();
		}else{
			
			$cb_expected_parameters = $expected_parameters;
			
		}
				
		self::$callbacks[ $cb_slug ] = array();
		
		self::$callbacks[ $cb_slug ]['reg_type'] = $from;
		
		self::$callbacks[ $cb_slug ]['expected_parameters'] = $cb_expected_parameters;
		
		self::$callbacks[ $cb_slug ]['callback_function'] = $callback;
		
		
		if(!isset($settings['title'])){
			$settings['title'] = 'Unknown Title';
		}
		if(!isset($settings['description'])){
			$settings['description'] = 'Unknown Description';
		}	
		self::$callbacks[ $cb_slug ]['settings'] = $settings;
		
	}
	
	
	
	private function save_query(){
		
		$query_parameters = array();
		
		$query_parameters['post_type'];
		
		
		if(isset($_POST['seramo_arg_post_type'])){
			
			if(is_array($_POST['seramo_arg_post_type'])){
				
				foreach($_POST['seramo_arg_post_type'] as $post_type){
					$query_parameters['post_type'][] = sanitize_text_field( $post_type );
				}
				
				if(count($query_parameters['post_type']) == 1){
					$query_parameters['post_type'] = $query_parameters['post_type'][0];
					if($query_parameters['post_type'] == ''){$query_parameters['post_type'] = 'any';}
				}
				
			}else{
				$query_parameters['post_type'] = sanitize_text_field( $_POST['seramo_arg_post_type'] );
			}
			
		}else{
			$query_parameters['post_type'] = 'any';
		}
		
		
		
	}
}