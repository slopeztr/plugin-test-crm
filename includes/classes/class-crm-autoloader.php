<?php

defined( 'ABSPATH' ) || exit;

class CRM_Autoloader {
	private $include_path = '';

	public function __construct() {
		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}

		spl_autoload_register( array( $this, 'autoload' ) );

		$this->include_path = CRM__PLUGIN_DIR . 'includes/classes/';
	}

	private function get_file_name_from_class( $class ) {
		return 'class-' . str_replace( '_', '-', $class ) . '.php';
	}

	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once $path;
			return true;
		}

		return false;
	}

	public function autoload( $class ) {
		$class = strtolower( $class );

		if ( 0 !== strpos( $class, 'crm_' ) ) {
			return;
		}

		$file = $this->get_file_name_from_class( $class );
		$path = '';

		if ( 0 === strpos( $class, 'crm_shortcode_' ) ) {
    	$path = $this->include_path . 'shortcodes/';
		} elseif ( 0 === strpos( $class, 'crm_admin_' ) ) {
      $path = $this->include_path . 'admin/';
    } elseif ( 0 === strpos( $class, 'crm_cli_' ) ) {
      $path = $this->include_path . 'cli/';
    }

		if ( empty( $path ) || ! $this->load_file( $path . $file ) ) {
			$this->load_file( $this->include_path . $file );
		}
	}
}

new CRM_Autoloader();
