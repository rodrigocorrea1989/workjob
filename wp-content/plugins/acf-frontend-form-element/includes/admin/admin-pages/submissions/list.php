<?php 
namespace ACFFrontend\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
} 

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/class-wp-list-table.php' );
}

if( ! class_exists( 'Submissions_List' ) ) :

	class Submissions_List extends \WP_List_Table {

		/** Class constructor */
		public function __construct() {
			parent::__construct( [
				'singular' => __( 'Submission', 'acf-frontend-form-element' ), //singular name of the listed records
				'plural'   => __( 'Submissions', 'acf-frontend-form-element' ), //plural name of the listed records
				'ajax'     => false //does this table support ajax?
			] );

		}


		/** Text displayed when no submission data is available */
		public function no_items() {
			_e( 'No submissions avaliable.', 'acf-frontend-form-element' );
		}



		function column_cb($item) {
			return sprintf(	'<input type="checkbox" name="submissions[]" value="%d" />', $item['id'] );    
		}

		/**
		 * Render a column when no column specific method exist.
		 *
		 * @param array $item
		 * @param string $column_name
		 *
		 * @return mixed
		 */
		public function column_default( $item, $column_name ) {
			switch( $column_name ){
				case 'created_at':
					$time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
					return date( $time_format, strtotime( $item[ $column_name ] ) );
				case 'title':
					if( $item[ $column_name ] ){
						$title = $item[ $column_name ];
					}else{
						$title = sprintf( 'Submission #%d', $item['id'] );
					}

					$title = sprintf( '<a href="?page=%s&action=%s&id=%s">%s</a>', $_REQUEST['page'], 'edit', $item['id'], $title );
					$actions = array(
						'edit' => sprintf('<a href="?page=%s&action=%s&id=%s">%s</a>', $_REQUEST['page'], 'edit', $item['id'], __( 'Review', 'acf-frontend-form-element' ) ),
						'delete' => sprintf('<a href="?page=%s&action=%s&id=%s&nonce=%s">%s</a>', $_REQUEST['page'], 'delete', $item['id'], wp_create_nonce( 'acff_delete_submission' ), __( 'Delete', 'acf-frontend-form-element' ) ),
					);
					return sprintf('%1$s %2$s', $title, $this->row_actions($actions));
				case 'user':
					$user = get_user_by( 'ID', $item[$column_name] );
					if( is_object( $user ) ){
						$title = $user->display_name . ' (' . $user->user_login . ')';
					}else{
						$title = __( 'Anonymous', 'acf-frontend-form-element' );
					}
					return $title;
				case 'status':
					$statuses = explode( ',', $item[$column_name] );
					$state = '';
					foreach( $statuses as $status ){
						$state .= $this->get_status_label( $status ) . '<br>';
					}
					return $state;
				default:
					return $item[ $column_name ];
			}
		}

		public function get_status_label( $slug ){
			switch( $slug ){
				case 'in_progress':
					return __( 'In Progress', 'acf-frontend-form-element' );
				case 'require_approval':
					return __( 'Pending Approval', 'acf-frontend-form-element' );
				case 'verify_email':
					return __( 'Pending Email Verification', 'acf-frontend-form-element' );
				case 'email_verified':
					return __( 'Email Verified', 'acf-frontend-form-element' );	
				case 'pending_payment':
					return __( 'Pending Payment', 'acf-frontend-form-element' );
				case 'approved':
					return __( 'Approved', 'acf-frontend-form-element' );
				default:
					return $slug;
			}
		} 

		function get_bulk_actions() {
			$actions = array(
				'bulk-delete'    => __( 'Delete', 'acf-frontend-form-element' ),
				'bulk-approve'	=> __( 'Approve', 'acf-frontend-form-element' ),
			);
		
			return $actions;
		}

		/**
		 * Gets the name of the default primary column.
		 *
		 * @since 4.3.0
		 *
		 * @return string Name of the default primary column, in this case, 'title'.
		 */
		protected function get_default_primary_column_name() {
			return 'title';
		}

		public function get_sortable_columns() {

			return array(
				'created_at'  => array( 'created_at', false ),
				'title' => array( 'title', false ),
				'form' => array( 'form', false ),
				'user' => array( 'user', false ),
			);

		}

		/**
		 *  Associative array of columns
		 *
		 * @return array
		 */
		function get_columns() {
			$columns = [
				'cb' => '<input type="checkbox" />',
				'title' => __( 'Title', 'acf-frontend-form-element' ),
				'user' => __( 'Submitted By', 'acf-frontend-form-element' ),
				'form' => __( 'Form', 'acf-frontend-form-element' ),
				'status' => __( 'Status', 'acf-frontend-form-element' ),
				'created_at' => __( 'Date', 'acf-frontend-form-element' ),
			];

			return $columns;
		}


		/**
		 * Handles data query and filter, sorting, and pagination.
		 */
		public function prepare_items() {

			$this->_column_headers = $this->get_column_info();

			/** Process bulk action */
			$this->process_bulk_action();

			$perpage     = $this->get_items_per_page( 'submissions_per_page', 20 );
			$current_page = $this->get_pagenum();
			$total_items  = acff()->submissions_handler->record_count();

			$this->set_pagination_args( [
				'total_items' => $total_items, //WE have to calculate the total number of items
				'per_page'    => $perpage //WE have to determine how many items to show on a page
			] );

			$this->items = acff()->submissions_handler->get_submissions( array( 'per_page' => $perpage, 'current_page' => $current_page ) );
			
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($columns, $hidden, $sortable);
		
		}

		public function process_bulk_action() {	
			// If the delete bulk action is triggered
			if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
				|| ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
			){

				$delete_ids = esc_sql( $_POST['submissions'] );

				// loop over the array of record IDs and delete them
				foreach( $delete_ids as $id ) {
					acff()->submissions_handler->delete_submission( absint( $id ) );
				}

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
					// add_query_arg() return the current url
					wp_redirect( esc_url_raw(add_query_arg()) );
				exit;
			}
			if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-approve' )
				|| ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-approve' )
			){

				$approve_ids = esc_sql( $_POST['submissions'] );

				// loop over the array of record IDs and delete them
				foreach( $approve_ids as $id ) {
					acff()->submissions_handler->approve_submission( absint( $id ) );

				}

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
					// add_query_arg() return the current url
					wp_redirect( esc_url_raw(add_query_arg()) );
				exit;
			}
		}

	}
	acff()->submissions_list = new Submissions_List;
endif;