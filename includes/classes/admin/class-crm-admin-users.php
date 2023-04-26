<?php

defined( 'ABSPATH' ) || exit;

class CRM_Admin_Users {
  public function __construct() {
    add_action( 'admin_bar_menu', array( $this, 'bar_menu' ), 999 ); // ocultar items en admin bar.
    add_action( 'admin_menu', array( $this, 'menu' ), 999 ); // ocultar items en admin menu.
    add_action( 'init', array( $this, 'add_permissions' ), 11 );
    //add_action( 'load-index.php', array( $this, 'dashboard_redirect' ), ); // redireccionar a contactos no index

    //restringir accesos
    add_action( 'load-options-general.php',  array( $this, 'restrict_user_access' ) );
    add_action( 'load-options-writing.php',  array( $this, 'restrict_user_access' ));
    add_action( 'load-options-reading.php',  array( $this, 'restrict_user_access' ) );
    add_action( 'load-options-discussion.php',  array( $this, 'restrict_user_access' ) );
    add_action( 'load-options-media.php',  array( $this, 'restrict_user_access' ) );
    add_action( 'load-options-permalink.php',  array( $this, 'restrict_user_access' ) );
    add_action( 'load-options.php',  array( $this, 'restrict_user_access' ) );
  }

  public function add_permissions() {
    remove_role( 'comercial' );
    $roles = array(
      'administrator' => 'Administrador',
    );

    foreach( $roles as $key => $role ) {
      add_role(
        $key,
        __( $role ), 
        array( 
          'read' => true, 
          'edit_posts' => true, 
          'publish_posts' => true, 
          'edit_published_posts' => true,
          'upload_files' => true,
          'delete_published_posts' => true,
          'manage_options' => true,
        )
      );

      $cap_role = get_role( $key );
      $cap_role->add_cap( 'read_private_project' );
      $cap_role->add_cap( 'publish_projects' );
      $cap_role->add_cap( 'edit_published_projects' );
      $cap_role->add_cap( 'edit_private_projects' );
      $cap_role->add_cap( 'edit_others_projects' );
      $cap_role->add_cap( 'edit_projects' );
      $cap_role->add_cap( 'delete_published_projects' );
      $cap_role->add_cap( 'delete_private_projects' );
      $cap_role->add_cap( 'delete_projects' );
      $cap_role->add_cap( 'delete_others_projects' );
    }
  }

  public function restrict_user_access() {
    if ( ! current_user_can( 'administrator' ) ) {
      wp_die( "No tienes permiso para estar aquí, por favor regresa :)" );

      exit();
    }
  }

  public function bar_menu( $wp_admin_bar ) {
    if ( ! current_user_can( 'administrator' ) ) {
      $wp_admin_bar->remove_node( 'updates' );
      $wp_admin_bar->remove_node( 'new-content' );

      // enfold.
      $wp_admin_bar->remove_node( 'avia' );
      $wp_admin_bar->remove_node( 'avia_ext' );
    }
  }

  public function menu() {
    global $submenu;

    if ( ! current_user_can( 'administrator' ) ) {
      // general wp
      remove_menu_page( 'options-general.php' );
      remove_menu_page( 'tools.php' );
      remove_menu_page( 'edit-comments.php' );
      remove_menu_page( 'edit.php' );
      remove_menu_page( 'upload.php' );
      //remove_menu_page( 'index.php' );

      // enfold items
      remove_menu_page( 'edit.php?post_type=portfolio' );
      remove_menu_page( 'avia' );
      remove_menu_page( 'layerslider' );

      // acf
      remove_menu_page( 'edit.php?post_type=acf-field-group' );

      // wp smtp
      remove_menu_page( 'wp-mail-smtp' );
    }

    if (
      current_user_can( 'cde' ) ||
      current_user_can( 'mercadeo' )
    ) {
      unset( $submenu[ 'crm' ][ 2 ] );
      unset( $submenu[ 'crm' ][ 3 ] );
    }
  }

  public function dashboard_redirect() {
    if ( ! current_user_can( 'administrator' ) ) {
      $screen = get_current_screen();

      if ( $screen->base == 'dashboard' ) {
        wp_redirect(
          admin_url( 'edit.php?post_type=contact' )
        );
      }
    }
  }
}

new CRM_Admin_Users();
