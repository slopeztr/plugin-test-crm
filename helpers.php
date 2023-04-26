<?php

if( !defined( 'ABSPATH' ) ) {
  exit;
}

if( !function_exists( 'get_elapsed_days' ) ) {
  function get_elapsed_days( $time ) {
    if( empty( $time ) ) {
      return false;
    }

    $date_timestamped = strtotime( $time );
    $current_date = current_time( 'timestamp' );

    $days_passed = round(
      ( $current_date - $date_timestamped ) / ( 60 * 60 * 24 )
    );

    if( $days_passed >= 0 && $days_passed <=15 ) {
      $days_passed = $days_passed . ( $days_passed == 1 ? ' day' : ' days' );

    } elseif( $days_passed >= 16 && $days_passed <=30 ) {
      $days_passed = $days_passed . ' days';

    } elseif( $days_passed > 30 ) {
      $days_passed = $days_passed . ' days';
    }

    return $days_passed;
  }
}

if( ! function_exists( 'crm_human_time' ) ) {
  function crm_human_time( $time ) {
    $time = current_time( 'timestamp') - $time;
    $time = ( $time < 1 )? 1 : $time;

    $tokens = array (
        2592000 => [ 'mes', 'meses' ],
        604800 => [ 'semana', 'semanas' ],
        86400 => [ 'día', 'días' ],
        3600 => [ 'hora', 'horas' ],
        60 => [ 'minuto', 'minutos' ],
        1 => [ 'segundo', 'segundos' ],
    );

    foreach ( $tokens as $unit=>$text) {
      if ( $time < $unit) {
        continue;
      }

      $num_units = floor( $time / $unit );

      return sprintf(
        _n(
          '%s ' . $text[0],
          '%s ' . $text[1],
          $num_units,
          CRM__PLUGIN_TEXTDOMAIN
        ),
        $num_units
      );
    }
  }
}

if( ! function_exists( 'crm_get_template' ) ) {
  function crm_get_template( $template_name, $args = array() ) {
    $default_path = CRM__PLUGIN_DIR . 'templates/';

    $template = $default_path . $template_name;
    $template = apply_filters( 'crm_locate_template', $template, $template_name, $default_path );
    // cómo agregar un nuevo directorio
    /*
    add_filter( 'crm_locate_template', function( $template, $template_name, $template_path ) {
      $plugin_path = PT__PLUGIN_DIR . 'templates/';

      if ( file_exists( $plugin_path . $template_name ) ) {
        $template = $plugin_path . $template_name;
      }

      return $template;
    },
    10, 3 );
    */

    if ( ! empty( $args ) && is_array( $args ) ) {
      extract( $args );
    }

    include $template;
  }
}

// custom debugger
if( !function_exists( 'wlog' ) ) {
  function wlog( $log ) {
	  if( WP_DEBUG === true ) {
		  if( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
			} else {
				error_log( $log );
			}
		}
	}
}
