<?php

defined( 'ABSPATH' ) || exit;

final class CRM {
  protected static $_instance = null;

  public static function instance() {
    if ( is_null( self::$_instance ) ) {
      self::$_instance = new self();
    }

    return self::$_instance;
  }

  public function __construct() {
    /*
     * Includes
     *
     */

    // base
    include_once CRM__PLUGIN_DIR . 'includes/classes/class-crm-autoloader.php';
    include_once CRM__PLUGIN_DIR . 'includes/classes/class-crm-post-types.php';

    // emails
    include_once CRM__PLUGIN_DIR . 'includes/classes/email/class-crm-email.php';
    include_once CRM__PLUGIN_DIR . 'includes/classes/email/class-crm-email-new-contact-assigned.php';
    include_once CRM__PLUGIN_DIR . 'includes/classes/email/class-crm-email-new-comment.php';

    // admin
    include_once CRM__PLUGIN_DIR . 'includes/classes/class-crm-admin.php';
    include_once CRM__PLUGIN_DIR . 'includes/classes/admin/class-crm-admin-projects.php';
    include_once CRM__PLUGIN_DIR . 'includes/classes/admin/class-crm-admin-users.php';
  }

  private function define( $name, $value ) {
    if( ! defined( $name ) ) {
      define( $name, $value );
    }
  }
}
