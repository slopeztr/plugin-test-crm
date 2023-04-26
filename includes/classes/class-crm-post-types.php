<?php

class CRM_Post_Types {
  public function __construct() {
    add_action( 'init', array( $this, 'register_post_types_project' ) );
  }

  public function register_post_types_project() {
    register_post_type( 'project', array(
      'labels' => array(
        'name' => _x( 'Project', 'post type general name', 'crm' ),
        'singular_name' => _x( 'Project', 'post type singular name', 'crm' ),
        'menu_name' => _x( 'Projects', 'admin menu', 'crm' ),
        'name_admin_bar' => _x( 'Project', 'add new on admin bar', 'crm' ),
        'add_new' => _x( 'Add new', 'project', 'crm' ),
        'add_new_item' => __( 'Add new Project', 'crm' ),
        'new_item' => __( 'New Project', 'crm' ),
        'edit_item' => __( 'Edit Project', 'crm' ),
        'view_item' => __( 'View Project', 'crm' ),
        'all_items' => __( 'All Projects', 'crm' ),
        'search_items' => __( 'Search Project', 'crm' ),
        'parent_item_colon' => __( 'Parent project:', 'crm' ),
        'not_found' => __( 'Project not found.', 'crm' ),
        'not_found_in_trash' => __( 'Project not found in trash.', 'crm' ),
      ),
      'has_archive' => false,
      'public' => true,
      'publicly_queryable' => false,
      'capability_type' => array( 'project', 'projects' ),
      'capabilities' => array(
        'edit_post' => 'edit_project',
        'read_post' => 'read_project',
        'delete_post' => 'delete_project',
        'edit_posts' => 'edit_projects',
        'edit_others_posts' => 'edit_others_projects',
        'publish_posts' => 'publish_projects',
        'read_private_posts' => 'read_private_projects',
        'create_posts' => 'edit_projects',
      ),
      'map_meta_cap' => true,
      'menu_icon' => 'dashicons-index-card',
      'menu_position' => 10,
      'query_var' => true,
      'show_in_menu' => false,
      'show_ui' => true,
      'show_in_rest' => true,
      'rest_base' => 'projects',
      'supports' => array(
        'comments',
        'custom-fields',
        'author',
      ),
    ));
  }
}

new CRM_Post_Types();
