<?php

defined( 'ABSPATH' ) || exit;

class CRM_Admin_Projects {
  public function __construct() {
    // lista
    add_action( 'pre_get_posts', array( $this, 'results_project_by_filter' ) );
    add_action( 'pre_get_posts', array( $this, 'results_project_by_role' ) );
    add_action( 'restrict_manage_posts', array( $this, 'restrict_manage_posts' ) );
    add_action( 'manage_project_posts_custom_column', array( $this, 'manage_project_posts_custom_column' ), 10, 2 ); //traer valores acf en columns
    add_filter( 'bulk_actions-edit-project', array( $this, 'bulk_actions_edit_project' ), 10 ); // remover edición y eliminar en lote
    add_filter( 'manage_edit-project_columns', array( $this, 'manage_edit_project_columns' ) );
    add_filter( 'list_table_primary_column', array( $this, 'list_table_primary_column' ), 10, 2 );

    // campos
    add_filter( 'acf/format_value/name=project_value', array( $this, 'change_format' ), 20, 3 ); // cambia el formato de valores numéricos para divisas.
    add_filter( 'acf/format_value/name=estimated_value', array( $this, 'change_format' ), 20, 3 ); // cambia el formato de valores numéricos para divisas.
    add_action( 'add_meta_boxes', array( $this, 'add_meta_box_project_info' ) ); // agregar una barra de progreso
    add_filter( 'acf/load_field/name=main_ally', array( $this, 'field_main_ally_choices' ) ); // cargar solo aliados
    add_filter( 'acf/load_field/name=builder', array( $this, 'field_builder_choices' ) ); // traer las orgs constructores
    add_filter( 'acf/load_field/name=dealer_employee', array( $this, 'field_dealer_employee_choices' ) ); // cargar contactos según la organización
    add_filter( 'acf/load_field/name=dealer', array( $this, 'field_dealer_choices' ) );

    // grabar
    add_action( 'acf/validate_save_post', array( $this, 'validate_save_post' ) ); // validar y guardar el flujo de caja.
    add_action( 'acf/save_post', array( $this, 'save_post' ), 5 ); // esta función se realiza cualquier operación antes de guardar.
    add_filter( 'wp_insert_post_data' , array( $this, 'wp_insert_post_data' ), 99, 1 ); // cambiar el titulo por defecto por el nombre del proyecto

    // ajax
    add_action( 'wp_ajax_project_get_contacts', array( $this, 'ajax_project_get_contacts' ) );
    add_action( 'wp_ajax_nopriv_project_get_contacts', array( $this, 'ajax_project_get_contacts' ) );
  }

  public function change_format( $value, $post_id, $field ) {
    $value = number_format( $value, 0, ',', '.' );

    return $value;
  }

  public function results_project_by_filter( $query ) {
    if( !is_admin() ) {
      return $query;
    }

    if( !$query->is_main_query() ) {
      return $query;
    }

    $screen = get_current_screen();
    if( $screen->post_type !== 'project' ) {
      return;
    }

    if( $screen->base !== 'edit' ) {
      return;
    }

    if(
      isset( $_GET[ 'by_author' ] ) &&
      $_GET[ 'by_author' ] != 'all'
    ) {
      $query->set( 'author', $_GET[ 'by_author' ] );
    }

    if(
      isset( $_GET[ 'by_zone' ] ) &&
      $_GET[ 'by_zone' ] != 'all'
    ) {
      $users = get_users(
        array(
          'role__in' => array(
            'comercial',
            'comercial-zona',
            'pm',
          ),
        ),
      );

      $user_group = array();
      foreach( $users as $user ) {
        $user_zone = get_field( 'zone', 'user_'. $user->ID );
        if( $user_zone == $_GET[ 'by_zone' ] ) {
          $user_group[] = $user->ID;
        }
      }

      $query->set( 'author__in', $user_group );
    }

    if(
      isset( $_GET[ 'by_status' ] ) &&
      !empty( $_GET[ 'by_status' ] )
    ) {
      $query->set( 'meta_query',
        array(
          array(
            'key' => 'status',
            'value' => $_GET[ 'by_status' ],
            'compare' => 'IN',
          ),
        ),
      );
    }
  }


  public function restrict_manage_posts() {
    $screen = get_current_screen();
    if( $screen->post_type !== 'project' ) {
      return;
    }

    if( $screen->base !== 'edit' ) {
      return;
    }

    $args = array(
      'by_zone' => FILTER_SANITIZE_STRING,
      'by_author' => FILTER_SANITIZE_STRING,
      'by_status' => array(
        'filter' => FILTER_VALIDATE_INT,
        'flags'  => FILTER_REQUIRE_ARRAY,
      ),
    );

    $inputs = filter_input_array(
      INPUT_GET,
      $args
    );

    $users = get_users(
      array(
        'role__in' => array(
          'comercial',
          'comercial-zona',
          'pm',
          'cde',
          'mercadeo',
          'gerencia-comercial',
          'gerencia-ceo',
        ),
      ),
    );

    $zones = get_field_object( 'field_62438463d6e01' ); // zone de pm
    $status = get_field_object( 'field_6279203fe9f9a' ); // status

    ob_start();
      include_once CRM__PLUGIN_DIR . 'templates/admin/filters/project_filters.php';
      $filters = ob_get_contents();
    ob_end_clean();

    echo $filters;
  }


  function validate_save_post() {
    // si campo forcast esta definido.
    if( isset( $_POST[ 'acf' ][ 'field_63067a4767d2a' ] ) ) {
      $forecast = $_POST[ 'acf' ][ 'field_63067a4767d2a' ];

      $values = array();
      foreach( $forecast as $rows ) {
        $values[] = $rows[ 'field_63067ac767d2c' ]; // installation_value
      }

      // validar si el valor estimado es igual a los valores de flujo de caja.
      if( $_POST[ 'acf' ][ 'field_62791fcde9f99' ] != array_sum( $values ) ) {
        acf_add_validation_error(
          'acf[field_63067a4767d2a]',
          'The summed values do not match the estimated ones.'
        );
      }
    }
  }

  public function add_meta_box_project_info() {
    global $post;

    if(
      isset( $post->ID ) &&
      get_post_status( $post->ID ) === 'publish'
    ) {
      $args = array(
        'estimated_value' => get_post_meta( $post->ID, 'estimated_value', true ),
        'project_progress' => get_post_meta( $post->ID, 'project_progress', true ),
        'status' => get_post_meta( $post->ID, 'status', true ),
      );

      add_meta_box(
        'box_project_progress_info',
        __( 'Progress','crm' ),
        array( $this, 'box_project_progress_info_content' ),
        'project',
        'normal',
        'high',
        $args
      );
    }
  }

  public function box_project_progress_info_content( $post, $callback_args ) {
    echo sprintf(
      '<label for="%1$s" id="%1$s">%2$d%%</label><progress id="%1$s" class="project_progress_bar" value="%3$d" max="%4$d">%2$d</progress>',
      $callback_args[ 'id' ],
      $callback_args[ 'args' ][ 'status' ],
      $callback_args[ 'args' ][ 'project_progress' ],
      $callback_args[ 'args' ][ 'estimated_value' ]
    );
  }

  public function save_post( $post_id ) {
    if( get_post_type() != 'project' ) {
      return;
    }

    // asignar un estimado cuando esta el valor asignado.
    if(
      isset( $_POST[ 'acf' ][ 'field_62791f83e9f98' ] ) &&
      isset( $_POST[ 'acf' ][ 'field_6287fb07d3e7d' ] )
    ) {
      //hallar el estimado del valor del proyecto.
      $_POST[ 'acf' ][ 'field_62791fcde9f99' ] = ( // valor del proyecto * porcentaje estimado.
        $_POST[ 'acf' ][ 'field_62791f83e9f98' ] * (float)$_POST[ 'acf' ][ 'field_6287fb07d3e7d' ]
      );
    }

    // asignar un valor de progreso.
    if(
      isset( $_POST[ 'acf' ][ 'field_62791fcde9f99' ] ) &&
      isset( $_POST[ 'acf' ][ 'field_6279203fe9f9a' ] )
    ) {
      $_POST[ 'acf' ][ 'field_62792189e9f9b' ] = ( // valor estimado * porcentaje estado.
        $_POST[ 'acf' ][ 'field_62791fcde9f99' ] * (int)$_POST[ 'acf' ][ 'field_6279203fe9f9a' ]
      ) / 100;
    }

    //actualizar ultimo contacto en proyecto
    $current_status = get_post_meta( $post_id, 'status', true );
    if(
      !empty( $_POST[ 'acf' ][ 'field_6279203fe9f9a' ] ) &&
      $current_status != $_POST[ 'acf' ][ 'field_6279203fe9f9a' ] // status nuevo.
    ) {
      // actualizar date_last_contact en proyecto
      $time = current_time( 'Y-m-d H:i:s' );
      $_POST[ 'acf' ][ 'field_628cee61c0c80' ] = $time;

      //actualizar novedades en organización
      if( !empty( $_POST[ 'acf' ][ 'field_6265f6dc5c8a9' ] ) ) { // organización dealer id.
        update_field(
          'last_contact_organization',
          $time,
          $_POST[ 'acf' ][ 'field_6265f6dc5c8a9' ]
        );
  
        // añadir link del proyecto a Novedad.
        $project_link = sprintf(
          '<a href="post.php?post=%1$s&action=edit">%2$s</a>',
          $post_id,
          $_POST[ 'acf' ][ 'field_6265f7a75c8aa' ] // name_project.
        );
  
        update_field(
          'last_update_detail',
          $project_link,
          $_POST[ 'acf' ][ 'field_6265f6dc5c8a9' ]
        );
      } 
    }

    // type_project es constructora.
    if(
      $_POST[ 'acf' ][ 'field_6265f7ff5c8ab' ] == 'Constructora' &&
      !empty( $_POST[ 'acf' ][ 'field_62b33004fc86a' ][ 'field_62b331aaa3895' ] ) // constructor_detail/unit_value_from
    ) {
      $_POST[ 'acf' ][ 'field_62b33004fc86a' ][ 'field_62b3361a24ec9' ] = ( // constructor_detail/value_m2
        // constructor_detail/unit_value_from / quantity_m2
        $_POST[ 'acf' ][ 'field_62b33004fc86a' ][ 'field_62b331aaa3895' ] / $_POST[ 'acf' ][ 'field_62b33004fc86a' ][ 'field_62b332a324ec8' ]
      );

      $_POST[ 'acf' ][ 'field_62b33004fc86a' ][ 'field_62b33934cb2bf' ] = ( // constructor_detail/final_value
        // constructor_detail/unit_value_from * number_units
        $_POST[ 'acf' ][ 'field_62b33004fc86a' ][ 'field_62b331aaa3895' ] * $_POST[ 'acf' ][ 'field_62b33004fc86a' ][ 'field_62b3385fcb2be' ]
      );
    }
  }

  public function field_main_ally_choices( $field ) {
    if( 'project' != get_post_type() ) {
      return $field;
    }

    $args = array(
      'numberposts' => -1,
      'post_type' => 'organization',
      'meta_query' => array(
        'relation' => 'AND',
        array(
          'key' => 'active',
          'value' => 'si',
          'compare' => '=',
        ),
        array(
          'key' => 'type_organization',
          'value' => 'Aliado',
          'compare' => '=',
        ),
      )
    );

    if(
      current_user_can( 'comercial-zona' ) ||
      current_user_can( 'pm' )
    ) {
      $user_zone = get_field( 'zone', 'user_'. get_current_user_id() );
      $args[ 'meta_query' ][] = array(
        'key' => 'zone',
        'value' => $user_zone,
        'compare' => '=',
      );
    }

    $organizations = get_posts( $args );
    $meta_org = '';
    if( is_array( $organizations ) ) {
      foreach( $organizations as $organization ) {
        $meta_org = get_post_meta(
          $organization->ID,
          'company',
          true
        );

        $field[ 'choices' ][ $organization->ID ] = $meta_org;
      }
    }

    return $field;
  }

  public function field_builder_choices( $field ) {
    if( 'project' != get_post_type() ) {
      return $field;
    }

    $args = array(
      'numberposts' => -1,
      'post_type' => 'organization',
      'meta_key' => 'type_organization',
      'meta_value' => 'Aliado',
      'meta_query' => array(
        'relation' => 'AND',
        array(
          'key' => 'type_ally',
          'value' => 'Constructores',
          'compare' => '=',
        ),
        array(
          'key' => 'active',
          'value' => 'si',
          'compare' => '=',
        ),
      ),
    );

    if(
      current_user_can( 'comercial' ) ||
      current_user_can( 'comercial-zona' ) ||
      current_user_can( 'pm' )
    ) {
      $user_zone = get_field( 'zone', 'user_'. get_current_user_id() );
      $args[ 'meta_query' ][] = array(
        'key' => 'zone',
        'value' => $user_zone,
        'compare' => '=',
      );
    }

    $organizations = get_posts( $args );
    $meta_org = '';
    if( is_array( $organizations ) ) {
      foreach( $organizations as $organization ) {
        $meta_org = get_post_meta(
          $organization->ID,
          'company',
          true
        );

        $field[ 'choices' ][ $organization->ID ] = $meta_org;
      }
    }

    return $field;
  }

  public function field_dealer_employee_choices( $field ) {
    global $post;

    if( !isset( $post->ID ) ) {
      return $field;
    }

    $dealer = get_field( 'dealer', $post->ID );
    $args = array(
      'numberposts' => -1,
      'post_type' => 'contact',
      'meta_key' => 'organization',
      'meta_value' =>  $dealer,
    );

    $contacts = get_posts( $args );
    foreach( $contacts as $contact ) {
      $meta_contact = get_post_meta(
        $contact->ID,
        'full_name',
        true
      );

      $field[ 'choices' ][ $contact->ID ] = $meta_contact;
    }

    return $field;
  }

  public function ajax_project_get_contacts() {
    if(
      !isset( $_POST[ 'project_nonce' ] ) ||
      !wp_verify_nonce( $_POST[ 'project_nonce' ], 'project_nonce' )
    ) {
      die( 'Permission denied' );
    }

    $args = array(
      'numberposts' => -1,
      'post_type' => 'contact',
      'meta_key' => 'organization',
      'meta_value' =>  $_POST[ 'dealer' ],
    );

    $contacts = get_posts( $args );

    $meta_contact = array();
    foreach( $contacts as $contact ) {
      $meta_contact[] = array(
        'post_id' => $contact->ID,
        'name' => get_post_meta(
          $contact->ID,
          'full_name',
          true
        ),
      );
    }

    wp_send_json_success( $meta_contact );

    die();
  }

  public function field_dealer_choices( $field ) {
    $args = array(
      'numberposts' => -1,
      'post_type' => 'organization',
      'meta_key' => 'type_organization',
      'meta_value' => 'Dealer',
      'meta_query' => array(
        'relation' => 'AND',
        array(
          'key' => 'active',
          'value' => 'si',
          'compare' => '=',
        ),
      ),
    );

    if( current_user_can( 'comercial' ) ) {
      $line_type = get_field( 'commercial_line', 'user_'. get_current_user_id() );
      $args[ 'meta_query' ][] = array(
        'key' => 'type_dealer',
        'value' => $line_type,
        'compare' => '=',
      );
    }

    if(
      current_user_can( 'comercial' ) ||
      current_user_can( 'comercial-zona' ) ||
      current_user_can( 'pm' )
    ) {
      $user_zone = get_field( 'zone', 'user_'. get_current_user_id() );
      $args[ 'meta_query' ][] = array(
        'key' => 'zone',
        'value' => $user_zone,
        'compare' => '=',
      );
    }

    $organizations = get_posts( $args );
    $meta_org = '';
    if( is_array( $organizations ) ) {
      foreach( $organizations as $organization ) {
        $meta_org = get_post_meta(
          $organization->ID,
          'company',
          true
        );

        $field[ 'choices' ][ $organization->ID ] = $meta_org;
      }
    }

    return $field;
  }


  public function results_project_by_role( $query ) {
    if( !is_admin() ) {
      return $query;
    }

    if( !$query->is_main_query() ) {
      return $query;
    }

    if(
      current_user_can( 'administrator' ) ||
      current_user_can( 'gerencia-comercial' ) ||
      current_user_can( 'gerencia-ceo' )
    ) {
      return $query;
    }

    if(
      isset( $query->query_vars[ 'post_type' ] ) &&
      $query->query_vars[ 'post_type' ] != 'project'
    ) {

      return $query;
    }

    $query->query_vars[ 'author' ] = get_current_user_id();
  }

  public function manage_project_posts_custom_column( $column, $post_id ) {
    $status = get_field( 'status', $post_id );
    $message = '';

    switch( $column ) :
      case 'dealer' :
        echo get_post_meta(
          get_field( $column, $post_id ),
          'company',
          true
        );
        break;
      case 'dealer_employee' :
        echo get_post_meta(
          get_field( $column, $post_id ),
          'full_name',
          true
        );
        break;
      case 'status' :
        echo $status . '%';
        break;
      case 'date_last_contact' :
        if( $status == 0 ) {
          $message = "Perdido <br />";
        } elseif( $status == 100 ) {
          $message = "Cerrado <br />";
        }

        echo $message . get_field( $column, $post_id );

        break;
      case 'days_passed' :
        if( !get_field( 'date_last_contact', $post_id ) ) {
          break;
        }

        if( $status == 0 ) {
          $class = 'is-due-lost';
          $message = 'Perdido';

          echo sprintf(
            '<div title="%1$s" class="score-icon %2$s"></div>',
            $message,
            $class
          );

          break;
        } elseif( $status == 100 ) {
          $class = 'is-due-closed';
          $message = 'Cerrado';

          echo sprintf(
            '<div title="%1$s" class="score-icon %2$s"></div>',
            $message,
            $class
          );

          break;
        }

        $days_passed = get_elapsed_days(
          get_field( 'date_last_contact', $post_id )
        );

        $number_days = (int) str_replace( array( ' day', ' days' ), '', $days_passed );
        if( $number_days >= 0 && $number_days <=15 ) {
          $class = 'is-due-started';
        } elseif( $number_days >= 16 && $number_days <= 30 ) {
          $class = 'is-due-soon';
        } elseif( $number_days > 30 ) {
          $class = 'is-due-past';
        }

        echo sprintf(
          '<div title="%1$s" class="score-icon %2$s"></div>',
          $days_passed !== false ? $days_passed : 'Sin días registrados',
          $class
        );

        break;
      default:
        echo get_field( $column, $post_id );
    endswitch;
  }


  public function bulk_actions_edit_project( $actions ) {
    if( !current_user_can( 'administrator' ) ) {
      unset( $actions[ 'edit' ] );
      unset( $actions[ 'trash' ] );
    }

    return $actions;
  }

  public function manage_edit_project_columns( $columns ) {
    unset( $columns );
    $columns[ 'cb' ] = '<input type="checkbox" />';
    $columns[ 'name_project' ] = __( 'Project', 'crm' );
    $columns[ 'project_value' ] = __( 'Project Value', 'crm' );
    $columns[ 'estimated_value' ] = __( 'Estimated', 'crm' );
    $columns[ 'status' ] = __( 'Status', 'crm' );

    if(
      current_user_can( 'administrator' )
    ) {
      $columns[ 'author' ] = __( 'Create by', 'crm' );
    }
    $columns[ 'date' ] = __( 'Entry Date', 'crm' );
    $columns[ 'date_last_contact' ] = __( 'Latest update', 'crm' );
    $columns[ 'days_passed' ] = __( '<span class="dashicons dashicons-dashboard"></span>', 'crm' );

    return $columns;
  }

  public function list_table_primary_column( $default, $screen ) {
    if( 'edit-project' === $screen ) {
      $default = 'name_project';
    }

    return $default;
  }

  public function wp_insert_post_data( $data ) {
    if( !isset( $data[ 'post_type' ] ) ) {
      return $data;
    }

    if( !isset( $_POST[ 'acf' ] ) ) {
      return $data;
    }

    if(
      $data[ 'post_type' ] == 'project' &&
      $data[ 'post_title' ] == 'Borrador automático'
    ) {
      $data[ 'post_title' ] = $_POST[ 'acf' ][ 'field_6265f7a75c8aa' ];
      $data[ 'post_name' ] = sanitize_title(
        $_POST[ 'acf' ][ 'field_6265f7a75c8aa' ]
      );
    }

    return $data;
  }
}

new CRM_Admin_Projects();
