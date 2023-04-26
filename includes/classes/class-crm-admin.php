<?php

defined( 'ABSPATH' ) || exit;

class CRM_Admin {
  public function __construct() {
    // filtros
    add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

    // estilo
    add_action( 'login_enqueue_scripts', array( $this, 'login_enqueue_scripts' ) ); //login styles.
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) ); // admin styles.
    add_action( 'admin_menu', array( $this, 'menu' ), 99 ); // admin menu.
    // add_action( 'admin_init', array( $this, 'css_color' ) ); // opciones de esquema de colores admin.

    add_action( 'current_screen', array( $this, 'current_screen' ) );
    add_filter( 'show_post_locked_dialog', array( $this, 'show_post_locked_dialog' ), 99, 3 ); // deshabilitar el bloqueo de pantalla en edición.
    add_filter( 'post_row_actions', array( $this, 'post_row_actions' ), 10, 2 ); //eliminar edición rapida y view no admin
    add_action( 'admin_print_scripts', array( $this, 'print_scripts_autosave' ) ); // deshabilitar guardado borrador automatico

    add_filter( 'views_edit-project', array( $this, 'views_edit' ) ); // eliminar los counts de los post.
    add_filter( 'views_edit-organization', array( $this, 'views_edit' ) ); // eliminar los counts de los post.
    add_filter( 'views_edit-contact', array( $this, 'views_edit' ) ); // eliminar los counts de los post.
    add_filter( 'views_edit-comments', array( $this, 'views_edit' ) ); // eliminar los counts de los post.

    add_filter( 'login_display_language_dropdown', '__return_false' ); // remover switch de idiomas en login.
    add_filter( 'login_headertext', function() { return 'Demo CRM'; }); // cambiar titulo del enlace logo login.
    add_filter( 'login_headerurl', function() { return 'https://demo.com/'; }); // url logo login.

    // comentarios
    add_action( 'comment_post', array( $this, 'comment_post' ), 10, 3 ); //según el comentario actualizar avance del proyecto u organización.
    add_action( 'comment_post', array( $this, 'comment_notification' ), 99, 3 ); //notifica asignación por correo
    add_filter( 'manage_edit-comments_columns', array( $this, 'manage_edit_comments_columns' ) );
    add_filter( 'comment_row_actions', array( $this, 'comment_row_actions' ), 10, 2 ); // El usuario solo puede modificar sus propios comentarios.
    add_filter( 'bulk_actions-edit-comments', array( $this, 'bulk_actions_edit_comments' ) );
    add_filter( 'pre_comment_user_ip', array( $this, 'pre_comment_user_ip' ) ); // remover la ip en los comentarios.


    /* TODO: Revisar.. mover a projects? */
    add_action( 'add_meta_boxes_project', array( $this, 'add_meta_boxes_project' ), 999 ); //posicionar metabox publicar al final.
    add_filter( 'wp_insert_post_data', array( $this, 'wp_insert_post_data' ) ); // chequear aprobado siempre el cajón de comentarios.

    add_filter( 'gettext', array( $this, 'gettext' ) ); // remover recuperación de contraseña.
  }


  public function pre_get_posts( WP_Query $wp_query ) {
    if( !is_admin() ) {
      return $wp_query;
    }

    if( !$wp_query->is_main_query() ) {
      return $wp_query;
    }

    if(
      in_array(
        $wp_query->get( 'post_type' ),
        array( 'contact', 'organization', 'project' )
      )
    ) {
      // desactivar el almacenamiento en caché para taxonomía.
      $wp_query->set( 'update_post_term_cache', false );
    }
  }

  public function current_screen() {
    if( current_user_can( 'administrator' ) ) {
      return;
    }

    $screen = get_current_screen();
    if( $screen->base == 'post' ) {
      add_action( 'admin_footer', array( $this, 'hide_options_submitdiv_metabox' ) );
    }
  }

  // remover edición de los campos del submitdiv metabox
  public function hide_options_submitdiv_metabox() {
?>
<script type="text/javascript">
(function( $ ) {
  'use strict';
  // Remove metabox options (savedraft, preview).
  $( '#submitdiv .edit-post-status' ).remove();
  $( '#submitdiv .edit-visibility' ).remove();
  // $( '#submitdiv .edit-timestamp' ).remove();
  $( '#minor-publishing-actions' ).remove();
  // $( '#major-publishing-actions' ).remove();

  // Agregue el botón "Agregar nuevo" en la columna de la derecha
  // $( '.wrap .page-title-action' ).clone().appendTo( '#side-sortables' );
})( jQuery );
</script>
<?php
  }

  public function add_meta_boxes_project() {
    remove_meta_box( 'submitdiv', 'project', 'side' );
    add_meta_box(
      'submitdiv',
      __( 'Publish' ),
      'post_submit_meta_box',
      'project',
      'normal',
      'low',
      null
    );
  }

  public function show_post_locked_dialog( $show, $post, $user ) {
    if( $post->post_type === 'project' ) {
      return false;
    }

    return $show;
  }

  public function comment_notification( $comment_ID, $comment_approved, $commentdata ) {
    $comment = get_comment( $comment_ID );
    if ( empty( $comment ) ) {
      return;
    }

    $post = get_post( $comment->comment_post_ID );
    if ( empty( $post ) ) {
      return;
    }

    if ( $post->post_author != $comment->user_id ) {
      do_action( 'crm_new_comment_notification', $comment );
    }
  }

  public function comment_post( $comment_ID, $comment_approved, $commentdata ) {
    $post = get_post( $commentdata[ 'comment_post_ID' ] );
    $author_id = get_post_field( 'post_author', $post->ID );

    if( $author_id != $commentdata[ 'user_id' ] ) {
      return;
    }

    if( $post->post_type == 'project' ) {
      update_field(
        'date_last_contact',
        $commentdata[ 'comment_date' ],
        $post->ID
      );
    }

    if( $post->post_type == 'organization' ) {
      update_field(
        'last_contact_organization',
        $commentdata[ 'comment_date' ],
        $post->ID
      );

      update_field(
        'last_update_detail',
        $commentdata[ 'comment_author' ] . (
          $commentdata[ 'comment_parent' ] == '0' ? ' ha comentado' : ' ha respondido'
        ),
        $post->ID
      );
    }
  }

  public function manage_edit_comments_columns( $columns ) {
    $columns[ 'response' ] = __( 'Proyecto', 'crm' );

    return $columns;
  }

  public function comment_row_actions( $actions, $comment ) {
    if( !current_user_can( 'administrator' ) ) {
      unset( $actions[ 'approve' ] );
      unset( $actions[ 'unapprove' ] );
      unset( $actions[ 'edit' ] );
      unset( $actions[ 'spam' ] );
      // unset( $actions[ 'quickedit' ] );
      // unset( $actions[ 'trash' ] );
    }

    if(
      get_comment_author_email( $comment->comment_ID ) !==
      wp_get_current_user()->user_email &&
      !current_user_can( 'administrator' )
    ) {
      unset( $actions[ 'approve' ] );
      unset( $actions[ 'unapprove' ] );
      unset( $actions[ 'quickedit' ] );
      unset( $actions[ 'edit' ] );
      unset( $actions[ 'spam' ] );
      unset( $actions[ 'trash' ] );
    }

    return $actions;
  }

  public function bulk_actions_edit_comments( $actions ) {
    if( !current_user_can( 'administrator' ) ) {
      unset( $actions[ 'unapprove' ] );
      unset( $actions[ 'approve' ] );
      unset( $actions[ 'spam' ] );
      unset( $actions[ 'trash' ] );
    }

    return $actions;
  }

  public function wp_insert_post_data( $data ) {
    if( $data[ 'post_type' ] == 'project' ) {
      $data[ 'comment_status' ] = 'open';
    }

    return $data;
  }

  public function pre_comment_user_ip( $comment_author_ip ) {
    return '';
  }

  public function views_edit( $views ) {
    if( current_user_can( 'administrator' ) ) {
      return $views;
    }

    // remover los tag ej: Borrador|Pendiente.
    unset( $views[ 'all' ] );
    unset( $views[ 'mine' ] );
    unset( $views[ 'draft' ] );
    unset( $views[ 'pending' ] );
    unset( $views[ 'future' ] );
    unset( $views[ 'trash' ] );

    $screen = get_current_screen();
    if( $screen->id == 'edit-comments' ) {
      unset( $views[ 'moderated' ] );
      unset( $views[ 'approved' ] );
      unset( $views[ 'spam' ] );
    } else {
      unset( $views[ 'publish' ] );
    }

    foreach( $views as $index => $view ) {
      $views[ $index ] = preg_replace(
        '/ <span class="count">\(.*?\)<\/span>/', '', $view
      );
    }

    return $views;
  }

  public function post_row_actions( $actions, $post ) {
    if( !current_user_can( 'administrator' ) ) {
      unset( $actions[ 'inline hide-if-no-js' ] );
      unset( $actions[ 'view' ] );
    }

    return $actions;
  }

  public function print_scripts_autosave() {
    global $post;

    $cpts = array(
      'contact',
      'organization',
      'project',
    );

    if(
      isset( $post->post_type ) &&
      in_array( $post->post_type, $cpts )
    ) {
      wp_deregister_script( 'autosave' );
    }
  }

  public function css_color() {
    global $_wp_admin_css_colors;

    $fresh_color_data = $_wp_admin_css_colors[ 'fresh' ];
    $_wp_admin_css_colors = array(
      'fresh' => $fresh_color_data,
      // 'midnight' => $midnight_color_data
    );

    // Custom colors
    wp_admin_css_color( 'Demo1', __( 'Demo1' ),
      CRM__PLUGIN_URL . 'assets/admin/css/schemes/hometech.css',
      array( '#ffffff', '#000000', '#ff6969' , '#35aae1')
    );

    wp_admin_css_color( 'Demo2', __( 'Demo2' ),
      CRM__PLUGIN_URL . 'assets/admin/css/schemes/control4.css',
      array( '#b82a30', '#ffffff', '#5c0101' , '#0e0e0e')
    );
  }

  public function menu() {
    global $menu;
    // renombrar menú "Escritorio"
    // $menu[ 2 ][ 0 ] = 'Dashboard';

    add_menu_page(
      'CRM', // page title
      'CRM', // menu title
      'manage_options', // capability
      'crm', // slug
      '',
      'dashicons-screenoptions', // icon slug
      1 // position
    );

    add_submenu_page(
      'crm',
      'Proyectos',
      'Proyectos',
      'manage_options',
      'edit.php?post_type=project',
      '',
      1
    );
  }

  public function gettext( $text ) {
    if( !in_array( $GLOBALS[ 'pagenow' ], array( 'wp-login.php' ) ) ) {
      return $text;
    }

    if( $text === '¿Has olvidado tu contraseña?' ) {
      $text = '';
    }

    return $text;
  }

  public function login_enqueue_scripts() {
    wp_enqueue_style(
      'crm-login',
      CRM__PLUGIN_URL . 'assets/admin/css/login.css',
      CRM__PLUGIN_VERSION
    );
  }

  public function enqueue_scripts( $hook ) {
    $assets = array(
      'stylesheets' => array(
        'general'
      ),
      'javascripts' => array()
    );

    $current_screen = get_current_screen();
    if ( isset( $current_screen->base ) && $current_screen->base === 'dashboard' ) {
      $assets[ 'stylesheets' ][] = 'dashboard';
    }

    if( is_admin() && 'contact' == get_post_type() ) {
      $assets[ 'stylesheets' ][] = 'contact';
      $assets[ 'javascripts' ][] = 'contact';
      $action = 'contact_get_contacts'; //función que se llama por ajax
    }

    if( is_admin() && 'organization' == get_post_type() ) {
      $assets[ 'stylesheets' ][] = 'organization';
    }

    if( is_admin() && 'project' == get_post_type() ) {
      $assets[ 'stylesheets' ][] = 'project';
      $assets[ 'javascripts' ][] = 'project';
      $assets[ 'javascripts' ][] = 'minify-multiselect-dropdown';
      $action = 'project_get_contacts';
    }

    $screen = get_current_screen();
    if( is_admin() && $screen->id == 'edit-comments' ) {
      $assets[ 'stylesheets' ][] = 'comments';
      $assets[ 'javascripts' ][] = 'comments';
    }

    extract( $assets );
    foreach( $stylesheets as $css ) {
      wp_enqueue_style(
        'crm-'. $css,
        CRM__PLUGIN_URL . 'assets/admin/css/"'. $css .'".css',
        // array( 'avia-merged-styles' ),
        CRM__PLUGIN_VERSION
      );
    }

    foreach( $javascripts as $js ) {
      wp_enqueue_script(
        'crm-'. $js,
        CRM__PLUGIN_URL . 'assets/admin/js/"'. $js .'".js',
        array( 'jquery' ),
        '1.0.0',
        true
      );

      if( $js == 'contact' || $js == 'project' ) {
        wp_localize_script(
          'crm-'. $js,
          $js .'_vars',
          array(
            'url' => admin_url( 'admin-ajax.php' ),
            $js . '_nonce' => wp_create_nonce( $js . '_nonce' ),
            'action' => $action,
          )
        );
      }
    }
  }
}

new CRM_Admin();
