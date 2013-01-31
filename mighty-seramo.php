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
	new mighty_seramo ();
}

class mighty_seramo {
	
	private $new_slug = null;
	
	private static $path;
	
	private static $url;
	
	const WP_OPTION_SERAMO_SLUG		= 'mighty_seramo_slug';
	const DEFAULT_SLUG				= 'seramo';
	
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
		
	}
	
	
	
	public function register_plugin_menu() {
		add_options_page ( 'Seramo', 'Seramo', 'manage_options', 'mighty-seramo-options', array ($this,'page_seramo_options') );
	}
	
	function page_seramo_options(){
		
		// load in the ui core and effects core
		wp_enqueue_script("jquery-ui-core");
		wp_enqueue_script("jquery-effects-core");
		
		wp_enqueue_script("jquery-ui-tabs");
		
		// load a jquery ui style from google
		wp_register_style('jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/themes/redmond/jquery-ui.css');
		wp_enqueue_style('jquery-ui-css');
		
		
		// load admin script
		wp_enqueue_script('mighty-seramo-admin', $this->url . 'mighty.seramo.admin.js');
		wp_enqueue_script('mighty-seramo-admin');
		
		$this->load_page ( 'options' );
	}
	
	private function load_page($page) {
		$page_file_path = $this->path . 'pages' . DIRECTORY_SEPARATOR . $page . '.php';
		if (file_exists ( $page_file_path )) {
			echo '<div id="mighty-page"><div class="wrap">';
			include_once $page_file_path;
			echo '</div></div>';
		} else {
			echo '<h1>Error, Page Cannot Be Found</h1>';
			echo $page_file_path;
		}
	}
	
	
	function form_save_slug(){
		
		$new_json_slug = sanitize_title( $_POST['new_slug'] );
		$new_slug = wp_unique_post_slug($new_json_slug, 0 , 'publish', 'page', 0);
		$this->set_new_slug( $new_slug );
		return exit ( $new_slug );
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
			
			$results = $_POST;
			
			return exit ( json_encode ( $results ) );
			
			exit ();
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
		
		$json_api_rules = array ("$base\$" => 'index.php?' . $base . '=catchall', "$base/(.+)\$" => 'index.php?' . $base . '=$matches[1]' );
		
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
}