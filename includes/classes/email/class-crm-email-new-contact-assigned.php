<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CRM_Email_New_Contact_Assigned extends CRM_Email {
	public function __construct() {
		$this->id = 'new_contact_assigned';
		$this->template  = 'new-contact-assigned.php';

		// triggers for this email.
		add_action( 'crm_new_contact_assigned_notification', array( $this, 'trigger' ), 10, 2 );
	}

	public function get_subject() {
		return 'Nuevo contacto asignado';
	}

	public function get_heading() {
		return 'Se te ha asignado un nuevo contacto';
	}

  public function get_content() {
    ob_start();
    crm_get_template(
			'emails/' . $this->template,
			array(
				'user' => $this->object,
				'post_id' => $this->post_id,
				'email_heading' => $this->get_heading(),
				'email' => $this,
			)
		);

    return ob_get_clean();
	}

  public function get_recipient() {
    return $this->object->user_email;
  }


	public function trigger( $user, $post_id ) {
    $this->object = $user;
    $this->post_id = $post_id;

    $this->send(
      $this->get_recipient(),
      $this->get_subject(),
      $this->get_content(),
      $this->get_headers()
    );
	}
}

new CRM_Email_New_Contact_Assigned();
