<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CRM_Email {
  public $id;
	public $heading = '';
	public $subject = '';
	public $template;
	public $recipient;
	public $object;
	public $post_id;

	public function __construct() {
  }

	public function get_subject() {
		return $this->subject;
	}

	public function get_heading() {
		return $this->heading;
	}

	public function get_recipient() {
		$recipients = array_map( 'trim', explode( ',', $this->recipient ) );
		$recipients = array_filter( $recipients, 'is_email' );

		return implode( ', ', $recipients );
	}

	public function get_headers() {
    $headers = array(
      'Content-Type: text/html',
      'Reply-to: ' . get_option( 'admin_email' ),
    );

		return $headers;
	}

	public function get_content() {
    return '';
	}

	public function get_from_name() {
		$from_name = get_option( 'blogname' );

		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}


	public function get_from_address() {
		$from_email = get_option( 'admin_email' );

		return sanitize_email( $from_email );
	}


	public function send( $to, $subject, $message, $headers ) {
		// error_log( $to . ", " . $subject . ", " . $message );
		
		$return = wp_mail( $to, $subject, $message, $headers );

		return $return;
	}
}
