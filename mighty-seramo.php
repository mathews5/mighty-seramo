<?php
/*
 * Plugin Name: Seramo by mightystudios URI: http://mightystudios.net
 * Description: Send JSON requests to WordPress and get a response! Version: 1.0
 * Author: mightystudios.net
 */

/**
 * Action to initialize the plugin
 */
add_action ( 'init', 'mighty_seramo_init' );

function mighty_seramo_init() {
	new mighty_seramo ();
}

class mighty_seramo {
	
	private $new_slug = null;
	
	const WP_OPTION_SERAMO_SLUG = 'mighty_seramo_slug';
	const DEFAULT_SLUG = 'seramo';
	
	function __construct() {
		
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
		
		add_filter ( 'query_vars', array (&$this, 'query_vars' ) );
		$this->setup_json ();
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
	 * Puts the custom msjson slug into the query vars
	 *
	 * TODO:: better description
	 *
	 * @return (string) field string
	 *        
	 */
	function query_vars($wp_vars) {
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