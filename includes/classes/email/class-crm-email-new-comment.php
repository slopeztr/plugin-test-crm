<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CRM_Email_New_Comment extends CRM_Email {
	public function __construct() {
		$this->id = 'new_comment';
		$this->template  = 'new-comment.php';

		// triggers for this email.
		add_action( 'crm_new_comment_notification', array( $this, 'trigger' ), 10, 1 );
	}

	public function get_subject() {
		return 'Nuevo comentario';
	}

	public function get_heading() {
		return 'Se te ha hecho un nuevo contacto';
	}

  public function get_content() {
		$post = get_post( $this->object->comment_post_ID );

    ob_start();
    crm_get_template(
			'emails/' . $this->template,
			array(
				'post' => $post,
				'email_heading' => $this->get_heading(),
				'email' => $this,
			)
		);

    return ob_get_clean();
	}

  public function get_recipient() {
    $recipient = get_option( 'admin_email' );
    $post = get_post( $this->object->comment_post_ID );

    if ( ! empty( $post ) ) {
      $user = get_user_by( 'id', $post->post_author );

      if ( ! empty( $user ) ) {
        $recipient = $user->user_email;
      }
    }

    return $recipient;
  }


	public function trigger( $comment ) {
    $this->object = $comment;

    $this->send(
      $this->get_recipient(),
      $this->get_subject(),
      $this->get_content(),
      $this->get_headers()
    );
	}
}

new CRM_Email_New_Comment();
