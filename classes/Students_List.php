<?php
namespace TUTOR;

if ( ! defined( 'ABSPATH' ) )
	exit;

use TUTOR\Backend_Page_Trait;

class Students_List {

	const STUDENTS_LIST_PAGE = 'tutor-students';

	/**
	 * Trait for utilities
	 *
	 * @var $page_title
	 */

	use Backend_Page_Trait;
	/**
	 * Page Title
	 *
	 * @var $page_title
	 */
	public $page_title;

	/**
	 * Bulk Action
	 *
	 * @var $bulk_action
	 */
	public $bulk_action = true;

	/**
	 * Handle dependencies
	 */
	public function __construct() {
		$this->page_title = __( 'Students', 'tutor' );
		/**
		 * Handle bulk action
		 *
		 * @since v2.0.0
		 */
		add_action( 'wp_ajax_tutor_student_bulk_action', array( $this, 'student_bulk_action' ) );
	}

	/**
	 * Prepare bulk actions that will show on dropdown options
	 *
	 * @return array
	 * @since v2.0.0
	 */
	public function prpare_bulk_actions(): array {
		$actions = array(
			$this->bulk_action_default(),
			$this->bulk_action_delete(),
		);
		return $actions;
	}


	/**
	 * Handle bulk action for student delete
	 *
	 * @return string JSON response.
	 * @since v2.0.0
	 */
	public function student_bulk_action() {
		// check nonce.
		tutor_utils()->checking_nonce();
		$status   = isset( $_POST['bulk-action'] ) ? sanitize_text_field( $_POST['bulk-action'] ) : '';
		$bulk_ids = isset( $_POST['bulk-ids'] ) ? sanitize_text_field( $_POST['bulk-ids'] ) : array();
		$update   = self::update_students( $status, $bulk_ids );
		return true === $update ? wp_send_json_success() : wp_send_json_error();
		exit;
	}

}
