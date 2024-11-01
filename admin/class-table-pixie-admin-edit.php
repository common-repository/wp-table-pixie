<?php

/**
 * The dashboard-specific edit functionality of the plugin.
 */
class Table_Pixie_Admin_Edit {

	/**
	 * The ID of this plugin.
	 */
	private $plugin_id;

	/**
	 * The version of this plugin.
	 */
	private $plugin_version;

	/**
	 * The Screen ID of the admin page.
	 */
	private $page_hook;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_id      The name of this plugin.
	 * @param string $plugin_version The version of this plugin.
	 */
	public function __construct( $plugin_id, $plugin_version ) {

		$this->plugin_id      = $plugin_id;
		$this->plugin_version = $plugin_version;
	}

	/**
	 * Is current screen ours?
	 *
	 * @return bool
	 */
	private function _is_our_screen() {
		if ( ! class_exists( 'Table_Pixie_Admin' ) ) {
			return false;
		}

		if ( ! method_exists( 'Table_Pixie_Admin', 'our_screen' ) ) {
			return false;
		}

		if ( Table_Pixie_Admin::our_screen( get_current_screen(), $this->page_hook ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Register the stylesheets for the Dashboard.
	 */
	public function enqueue_styles() {
		if ( ! $this->_is_our_screen() ) {
			return;
		}

		wp_enqueue_style(
			$this->plugin_id . '-bootstrap',
			plugin_dir_url( __FILE__ ) . 'bootstrap/css/bootstrap.css',
			array(),
			$this->plugin_version,
			'all'
		);

		wp_enqueue_style(
			$this->plugin_id . '-admin-edit',
			plugin_dir_url( __FILE__ ) . 'css/table-pixie-admin-edit.css',
			array(),
			$this->plugin_version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the dashboard.
	 */
	public function enqueue_scripts() {
		if ( ! $this->_is_our_screen() ) {
			return;
		}

		wp_enqueue_script(
			$this->plugin_id . '-bootstrap',
			plugin_dir_url( __FILE__ ) . 'bootstrap/js/bootstrap.js',
			array( 'jquery' ),
			$this->plugin_version
		);

		wp_enqueue_script(
			$this->plugin_id . '-admin-edit',
			plugin_dir_url( __FILE__ ) . 'js/table-pixie-admin-edit.js',
			array( 'jquery', 'backbone', $this->plugin_id . '-bootstrap' ),
			$this->plugin_version,
			true
		);

		$data = array(
			'strings' => array(
				'add_record'    => __( 'Add Record', 'wp-table-pixie' ),
				'edit_record'   => __( 'Edit Record', 'wp-table-pixie' ),
				'unknown_error' => __( 'Oops, something went wrong. Please check your browser console.', 'wp-table-pixie' ),
			),
			'nonce'   => wp_create_nonce( 'table-pixie-admin-edit-nonce' ),
		);
		wp_localize_script( $this->plugin_id . '-admin-edit', 'TablePixieAdminEdit', $data );
	}

	/**
	 * Include our HTML templates.
	 *
	 * @param $data
	 */
	public function add_templates( $data ) {
		if ( ! $this->_is_our_screen() ) {
			return;
		}

		include plugin_dir_path( __FILE__ ) . 'partials/table-pixie-admin-edit-tmpl.php';
	}

	/**
	 * Handler for the table_pixie_admin_title filter.
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public function admin_title( $title ) {
		global $wp_version;

		$link_class = 'page-title-action';
		if ( version_compare( $wp_version, '4.3-dev', '<' ) ) {
			$link_class = 'add-new-h2';
		}

		$blog_id = empty( $_REQUEST['blog_id'] ) ? '' : sanitize_key( $_REQUEST['blog_id'] );
		$table   = empty( $_REQUEST['table'] ) ? '' : sanitize_key( $_REQUEST['table'] );
		$add_url = add_query_arg( 'action', 'table_pixie_admin_edit_add', $_SERVER['REQUEST_URI'] );

		$params = "'$table'";

		if ( ! empty( $blog_id ) ) {
			$params .= ", $blog_id";
		}

		$add_new = sprintf(
			'<a onclick="return TablePixieAdminEdit.showEdit( ' . $params . ' );" href="%s" class="' . $link_class . '">%s</a>',
			$add_url,
			__( 'Add New', 'wp-table-pixie' )
		);

		return $title . ' ' . $add_new;
	}

	/**
	 * The admin page has been hooked.
	 *
	 * @param bool|string $page_hook The admin page's hook name.
	 */
	public function admin_page_hooked( $page_hook ) {
		$this->page_hook = $page_hook;

		add_filter( 'screen_settings', array( $this, 'screen_settings' ), 10, 2 );
	}

	/**
	 * Handles screen_settings filter to add screen settings to Screen Options panel.
	 *
	 * @param string    $screen_settings
	 * @param WP_Screen $screen
	 *
	 * @return string
	 */
	public function screen_settings( $screen_settings, $screen ) {
		// Only add our extra screen settings when on our screen.
		if ( empty( $this->page_hook ) || ! $this->_is_our_screen() ) {
			return $screen_settings;
		}

		$delete_protected = false;
		$options          = get_user_option( 'table_pixie_screen_options' );

		if ( false !== $options && isset( $options['delete_protected'] ) && is_bool( $options['delete_protected'] ) ) {
			$delete_protected = $options['delete_protected'];
		}

		$checked         = $delete_protected ? ' checked="checked"' : '';
		$screen_settings .= '<label for="delete-protected">';
		$screen_settings .= '<input class="delete-protected-tog" name="delete_protected" type="checkbox" id="delete-protected"' . $checked . '>';
		$screen_settings .= __( 'Allow update & deletion of protected meta', 'wp-table-pixie' );
		$screen_settings .= '</label>';

		return $screen_settings;
	}

	/**
	 * Handle AJAX requests to save state of allow deletion of protected meta screen option.
	 */
	public function ajax_toggle_delete_protected() {
		check_ajax_referer( 'table-pixie-nonce', 'nonce' );
		$options = get_user_option( 'table_pixie_screen_options' );

		$options['delete_protected'] = 'true' == $_REQUEST['delete_protected'];

		update_user_option( get_current_user_id(), 'table_pixie_screen_options', $options );
		wp_die();
	}

	/**
	 * Add bulk actions that can be handled.
	 *
	 * @param array $actions
	 *
	 * @return array
	 */
	public function get_bulk_actions( $actions = array() ) {
		$actions['table_pixie_admin_edit_delete']         = __( 'Delete Permanently', 'wp-table-pixie' );
		$actions['table_pixie_admin_edit_fix_serialized'] = __( 'Fix Serialized', 'wp-table-pixie' );

		return $actions;
	}

	/**
	 * Process the given row or bulk action.
	 *
	 * @param bool   $redirect
	 * @param string $action  The action to be performed.
	 * @param array  $ids     The record ids to be processed.
	 * @param int    $blog_id Blog ID.
	 * @param string $table   The table where the record ids belong.
	 *
	 * @return bool
	 */
	public function process_action( $redirect, $action, $ids, $blog_id, $table ) {
		if ( empty( $action ) || empty( $ids ) || ! $this->_valid_table( $table ) ) {
			return $redirect;
		}

		$result = false;

		switch ( $action ) {
			case 'table_pixie_admin_edit_delete':
				$result = $this->_delete_by_ids( $ids, $table, $blog_id );
				break;
			case 'table_pixie_admin_edit_fix_serialized':
				$result = $this->_fix_serialized_by_ids( $ids, $table, $blog_id );
				break;
		}

		if ( $result ) {
			$redirect = true;
		}

		return $redirect;
	}

	/**
	 * Delete one or more records by their IDs.
	 *
	 * @param int|array $ids
	 * @param string    $table
	 * @param int       $blog_id
	 *
	 * @return bool
	 */
	private function _delete_by_ids( $ids, $table, $blog_id = null ) {
		if ( empty( $ids ) ) {
			return false;
		}

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		if ( empty( $blog_id ) ) {
			$blog_id = null;
		}

		$expected = count( $ids );
		$actual   = 0;
		foreach ( $ids as $__tp_id ) {
			if ( $this->_delete_item( array( 'blog_id' => $blog_id, 'table' => $table, '__tp_id' => $__tp_id ) ) ) {
				$actual++;
			}
		}

		$result = ( 0 < $actual );

		if ( $result ) {
			$type    = 'updated';
			$message = __( 'Record deleted.', 'wp-table-pixie' );
			if ( 1 < $actual ) {
				$message = sprintf( __( '%1$d records deleted.', 'wp-table-pixie' ), $actual );
			}
		} else {
			$type    = 'error';
			$message = __( 'Could not delete record.', 'wp-table-pixie' );
			if ( 1 < $expected ) {
				$message = sprintf( __( '%1$d of %2$d records deleted.', 'wp-table-pixie' ), $actual, $expected );
			}
		}

		// We've at least tried to process something.
		return $this->_add_notice( $message, $type );
	}

	/**
	 * If the given string is broken (optionally base64 encoded) serialized data, fix it, otherwise return untouched.
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	private static function _maybe_fix_serialized_string( $input ) {
		if ( ! is_string( $input ) ) {
			return $input;
		}

		$output = $input;

		$base64 = false;
		if ( Table_Pixie_Data_Format::is_base64( $output ) ) {
			$base64 = true;
			$output = base64_decode( $output, true );
		}

		if ( ! Table_Pixie_Data_Format::is_broken_serialized( $output ) ) {
			return $input;
		}

		$output = preg_replace_callback( '/s:(\d+):"(.*?)";/', array(
			'Table_Pixie_Admin_Edit',
			'_fix_serialized_matches',
		), $output );

		if ( ! is_serialized( $output ) || Table_Pixie_Data_Format::is_broken_serialized( $output ) ) {
			return $input;
		}

		// If it was base64 encoded then re-encode.
		if ( $base64 ) {
			$output = base64_encode( $output );
		}

		return $output;
	}

	/**
	 * Fixes the broken string segments of a serialized data string.
	 *
	 * @param $matches array of matched parts.
	 *
	 * @return string
	 */
	private static function _fix_serialized_matches( $matches ) {
		return 's:' . strlen( $matches[2] ) . ':"' . $matches[2] . '";';
	}

	/**
	 * Fix one or more broken serialized values selected by their IDs.
	 *
	 * @param int|array $ids
	 * @param string    $table
	 * @param int       $blog_id
	 *
	 * @return bool
	 */
	private function _fix_serialized_by_ids( $ids, $table, $blog_id = null ) {
		if ( empty( $ids ) ) {
			return false;
		}

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		if ( empty( $blog_id ) ) {
			$blog_id = null;
		}

		if ( is_numeric( $blog_id ) && is_multisite() ) {
			$blog_id = (int) $blog_id;
			switch_to_blog( $blog_id );
		}

		$expected = count( $ids );
		$actual   = 0;
		foreach ( $ids as $__tp_id ) {
			$item = $this->_get_item( array( 'blog_id' => $blog_id, 'table' => $table, '__tp_id' => $__tp_id ) );

			$result = false;
			if ( ! empty( $item ) && $item->__tp_id === $__tp_id && ! empty( $item->meta_value ) ) {
				$meta_value = self::_maybe_fix_serialized_string( $item->meta_value );

				// Not changed, don't update record.
				if ( $meta_value === $item->meta_value ) {
					continue;
				}

				// If it is serialized, prepare for auto-serialization when updating record.
				if ( is_serialized( $meta_value ) ) {
					$meta_value = Table_Pixie_Data_Format::maybe_unserialize( $meta_value );
				}

				if ( 'options' === $table ) {
					$result = update_option( $item->meta_key, $meta_value );
				} else {
					$meta_type = $this->_meta_type_for_table( $table );
					$result    = update_metadata_by_mid( $meta_type, $__tp_id, $meta_value );
				}
			}

			if ( $result ) {
				$actual++;
			}
		}

		$result = ( 0 < $actual );

		if ( $result ) {
			$type    = 'updated';
			$message = __( 'Record fixed.', 'wp-table-pixie' );
			if ( 1 < $actual ) {
				$message = sprintf( __( '%1$d records fixed.', 'wp-table-pixie' ), $actual );
			}
		} else {
			$type    = 'error';
			$message = __( 'Could not fix record.', 'wp-table-pixie' );
			if ( 1 < $expected ) {
				$message = sprintf( __( '%1$d of %2$d records fixed.', 'wp-table-pixie' ), $actual, $expected );
			}
		}

		if ( is_numeric( $blog_id ) && is_multisite() ) {
			restore_current_blog();
		}

		// We've at least tried to process something.
		return $this->_add_notice( $message, $type );
	}

	/**
	 * Returns transient key name base for storing notices for current user.
	 *
	 * @return string
	 */
	private function _get_notices_key() {
		static $transient_key;

		if ( empty( $transient_key ) ) {
			$transient_key = $this->plugin_id . '_notices_' . get_current_user_id();
		}

		return $transient_key;
	}

	/**
	 * Add a notice to be displayed in the admin dashboard for the current user.
	 *
	 * @param string    $message
	 * @param string    $type
	 * @param bool|true $dismissible
	 *
	 * @return bool
	 */
	private function _add_notice( $message, $type = 'updated', $dismissible = true ) {
		$notices = get_site_transient( $this->_get_notices_key() );

		if ( false === $notices ) {
			unset( $notices );
		}

		$notices[] = $this->_create_notice_array( $message, $type, $dismissible );

		return set_site_transient( $this->_get_notices_key(), $notices, 120 );
	}

	/**
	 * Create notice array structure.
	 *
	 * @param string    $message
	 * @param string    $type
	 * @param bool|true $dismissible
	 *
	 * @return array
	 */
	private static function _create_notice_array( $message, $type = 'updated', $dismissible = true ) {
		return array(
			'message'     => $message,
			'type'        => $type,
			'dismissible' => $dismissible,
		);
	}

	/**
	 * Format a notice.
	 *
	 * @param array $notice
	 *
	 * @return string
	 */
	private static function _format_notice( $notice ) {
		$output  = '';
		$message = empty( $notice['message'] ) ? '' : $notice['message'];

		if ( ! empty( $message ) ) {
			$type        = empty( $notice['type'] ) ? 'updated' : $notice['type'];
			$dismissible = empty( $notice['dismissible'] ) ? '' : ' is-dismissible';

			$output = '<div class="' . $type . ' notice' . $dismissible . '"><p>' . esc_html( $message ) . '</p></div>';
		}

		return $output;
	}

	/**
	 * Handler for the "admin_notices" action to display any queued up admin notices.
	 */
	public function admin_notices() {
		$notices = get_site_transient( $this->_get_notices_key() );

		if ( empty( $notices ) || ! is_array( $notices ) ) {
			return;
		}

		foreach ( $notices as $notice ) {
			$output = $this->_format_notice( $notice );

			if ( ! empty( $output ) ) {
				echo $output;
			}
		}

		delete_site_transient( $this->_get_notices_key() );
	}

	/**
	 * Adds plugin's help tabs if on correct screen.
	 *
	 * @param array $help_tabs existing help tabs.
	 *
	 * @return array
	 */
	public function set_help_tabs( $help_tabs ) {
		$help_tabs[] = array(
			'id'      => 'table-pixie-admin-edit-help-add-new',
			'title'   => __( 'Add New', 'wp-table-pixie' ),
			'content' => __( '
					<p>
						You can create a new record by using the "Add New" button to the right of the plugin\'s title.<br>
						A panel will slide into view where you can enter the Key, Value, Related ID/Autoload and Base64 Encode values for a record.<br>
						Please see the <strong>Columns</strong> help section for more information on the Key, Value and Related ID/Autoload fields.
					</p>
				',
				'wp-table-pixie'
			),
		);

		$help_tabs[] = array(
			'id'      => 'table-pixie-admin-edit-help-bulk-actions',
			'title'   => __( 'Bulk Actions', 'wp-table-pixie' ),
			'content' => __( '
					<p>
						<dl>
							<dt>Delete Permanently</dt>
							<dd>You can delete multiple records in one go by selecting their respective checkboxes in the first column of the table and selecting "Delete Permanently" from the Bulk Actions select list at the top or bottom of the table, use the "Apply" button to then permanently delete the selected records.</dd>
						</dl>
						<dl>
							<dt>Fix Serialized</dt>
							<dd>
								You can fix multiple broken serialized records in one go by selecting their respective checkboxes in the first column of the table and selecting "Fix Serialized" from the Bulk Actions select list at the top or bottom of the table, use the "Apply" button to then fix any selected broken serialized records.<br>
								If a selected record does not contain a broken serialized string it will be skipped.<br>
								Broken serialized data that is base64 encoded will be decoded, fixed, and then re-encoded.
							</dd>
						</dl>
					</p>
				',
				'wp-table-pixie'
			),
		);

		$help_tabs[] = array(
			'id'      => 'table-pixie-admin-edit-help-row-actions',
			'title'   => __( 'Row Actions', 'wp-table-pixie' ),
			'content' => __( '
					<p>
						<dl>
							<dt>Edit</dt>
							<dd>
								You can edit an existing record by hovering over the Key column for the record and clicking the "Edit" link.<br>
								A panel will slide into view where you can edit the Key, Value, Related ID/Autoload and Base64 Encode values for a record.<br>
								For some very important records you will only be able to edit the Value to help guard against potential problems.<br>
								You should however be <strong>very careful</strong> when editing any record to make sure you enter valid values.<br>
								Only change the Related ID/Autoload and Base64 Encode settings if you are absolutely sure they need changing.<br>
								Please see the <strong>Columns</strong> help section for more information on the Key, Value and Related ID/Autoload fields.
							</dd>
						</dl>
						<dl>
							<dt>Delete Permanently</dt>
							<dd>
								You can delete a record by hovering over the Key column for the record and clicking the "Delete Permanently" link.<br>
								For some very important records the "Delete Permanently" row action will not be available.
							</dd>
						</dl>
						<dl>
							<dt>Fix Serialized</dt>
							<dd>
								You can fix a broken serialized record by hovering over the Key column for the record and clicking the "Fix Serialized" link.<br>
								Broken serialized data that is base64 encoded will be decoded, fixed, and then re-encoded.<br>
								If a record does not contain serialized data then the "Fix Serialized" row action will not be available.
							</dd>
						</dl>
					</p>
				',
				'wp-table-pixie'
			),
		);

		return $help_tabs;
	}

	/**
	 * Handler for table_pixie_column_row_actions filter.
	 *
	 * @param mixed  $actions
	 * @param object $item
	 * @param array  $options
	 *
	 * @return string
	 */
	public function column_row_actions( $actions, $item, $options = array() ) {
		if ( empty( $item ) || empty( $options['column'] ) || ! isset( $item->{$options['column']} ) || empty( $item->{$options['column']} ) ) {
			return $actions;
		}

		switch ( $options['column'] ) {
			case 'meta_key':
				// Edit link.
				$edit_url = add_query_arg( 'action', 'table_pixie_admin_edit_edit', $_SERVER['REQUEST_URI'] );
				$edit_url = add_query_arg( 'record', $item->__tp_id, $edit_url );
				$blog_id  = empty( $_REQUEST['blog_id'] ) ? '' : sanitize_key( $_REQUEST['blog_id'] );
				$table    = empty( $_REQUEST['table'] ) ? '' : sanitize_key( $_REQUEST['table'] );

				$actions['edit'] = sprintf(
					'<a onclick="return TablePixieAdminEdit.showEdit( \'%1$s\', %2$d, %3$d );" href="%4$s">%5$s</a>',
					$table,
					$blog_id,
					$item->__tp_id,
					$edit_url,
					__( 'Edit', 'wp-table-pixie' )
				);

				// Delete Permanently link.
				if ( $this->_meta_key_deletable( $table, $item->meta_key ) ) {
					$delete_url = add_query_arg( 'action', 'table_pixie_admin_edit_delete', $_SERVER['REQUEST_URI'] );
					$delete_url = add_query_arg( 'record', $item->__tp_id, $delete_url );

					$actions['delete'] = sprintf(
						'<a onclick="return showNotice.warn();" href="%s">%s</a>',
						$delete_url,
						__( 'Delete Permanently', 'wp-table-pixie' )
					);
				}

				// Fix Serialized link.
				if ( Table_Pixie_Data_Format::contains_serialized( $item->meta_value ) ) {
					$fix_serialized_url = add_query_arg( 'action', 'table_pixie_admin_edit_fix_serialized', $_SERVER['REQUEST_URI'] );
					$fix_serialized_url = add_query_arg( 'record', $item->__tp_id, $fix_serialized_url );

					$actions['fix_serialized'] = sprintf(
						'<a href="%s">%s</a>',
						$fix_serialized_url,
						__( 'Fix Serialized', 'wp-table-pixie' )
					);
				}
				break;
		}

		return $actions;
	}

	/**
	 * Returns an array of meta_keys that are not deletable.
	 *
	 * @param string $table
	 *
	 * @return array
	 */
	private function _delete_blacklist( $table ) {
		static $blacklist;
		static $_table = '';

		if ( null === $blacklist || $_table !== $table ) {
			$_table     = $table;
			$_blacklist = array();

			if ( 'options' === $table ) {
				$_blacklist = array(
					'siteurl',
					'home',
					'active_plugins',
				);
			}

			$blacklist = apply_filters( 'table_pixie_admin_edit_delete_blacklist', $_blacklist, $table );

			if ( empty( $blacklist ) || ! is_array( $blacklist ) ) {
				$blacklist = $_blacklist;
			}
		}

		return $blacklist;
	}

	/**
	 * Is given meta_key deletable?
	 *
	 * @param string $table
	 * @param string $meta_key
	 *
	 * @return bool
	 */
	private function _meta_key_deletable( $table, $meta_key ) {
		$meta_type        = $this->_meta_type_for_table( $table );
		$options          = get_user_option( 'table_pixie_screen_options' );
		$delete_protected = isset( $options['delete_protected'] ) ? $options['delete_protected'] : false;

		if ( ! empty( $meta_type ) &&
		     ! empty( $table ) &&
		     ! empty( $meta_key ) &&
		     (
			     ( ! $delete_protected && 'options' !== $table && is_protected_meta( $meta_key, $meta_type ) ) ||
			     in_array( $meta_key, $this->_delete_blacklist( $table ) )
		     )
		) {
			return false;
		}

		return true;
	}

	/**
	 * Send a response to ajax request, as JSON.
	 *
	 * @param mixed $response
	 */
	private function _ajax_return( $response = true ) {
		if ( is_a( $response, 'WP_Error' ) ) {
			$code = $response->get_error_code();

			if ( is_int( $code ) ) {
				status_header( $code );
			}

			$response = array(
				'error' => $response->get_error_message(),
			);
		}

		echo json_encode( $response );
		exit;
	}

	/**
	 * Process a wp_ajax_table_pixie_admin_edit_options action.
	 */
	public function ajax_records() {
		if ( empty( $_REQUEST['action_type'] ) ) {
			$this->_ajax_return( new WP_Error( 406, __( 'An "action_type" was not specified.', 'wp-table-pixie' ) ) );
		}

		if ( empty( $_REQUEST['model'] ) ) {
			$this->_ajax_return( new WP_Error( 406, __( 'A "model" was not specified.', 'wp-table-pixie' ) ) );
		}

		$action_type = trim( $_REQUEST['action_type'] );
		if ( ! in_array( $action_type, array( 'create', 'read', 'update', 'delete' ) ) ) {
			$this->_ajax_return( new WP_Error( 406, __( 'The specified "action_type" was invalid.', 'wp-table-pixie' ) ) );
		}

		$model = $_REQUEST['model'];

		if ( ! is_array( $model ) ) {
			$this->_ajax_return( new WP_Error( 406, __( 'The specified model was not an array.', 'wp-table-pixie' ) ) );
		}

		// Check and sanitize blog_id.
		if ( is_multisite() && empty( $model['blog_id'] ) ) {
			$this->_ajax_return( new WP_Error( 406, __( 'A "blog_id" value was not specified.', 'wp-table-pixie' ) ) );
		} else {
			$model['blog_id'] = empty( $model['blog_id'] ) ? '' : sanitize_key( $model['blog_id'] );
		}

		// Check and sanitize table.
		if ( is_multisite() && empty( $model['table'] ) ) {
			$this->_ajax_return( new WP_Error( 406, __( 'A "table" value was not specified.', 'wp-table-pixie' ) ) );
		} else {
			$model['table'] = empty( $model['table'] ) ? '' : sanitize_key( $model['table'] );
		}

		// Check and sanitize __tp_id.
		if ( in_array( $action_type, array( 'read', 'update', 'delete' ) ) && empty( $model['__tp_id'] ) ) {
			$this->_ajax_return( new WP_Error( 406, __( 'A "__tp_id" value was not specified.', 'wp-table-pixie' ) ) );
		} else {
			$model['__tp_id'] = empty( $model['__tp_id'] ) ? '' : sanitize_key( $model['__tp_id'] );
		}

		if ( in_array( $action_type, array( 'create', 'update', 'delete' ) ) ) {
			// Check and sanitize meta_key, but allow uppercase letters in keys.
			if ( empty( $model['meta_key'] ) ) {
				$this->_ajax_return( new WP_Error( 406, __( 'A "meta_key" value was not specified.', 'wp-table-pixie' ) ) );
			}
			if ( sanitize_key( trim( $model['meta_key'] ) ) !== strtolower( trim( $model['meta_key'] ) ) ) {
				$this->_ajax_return( new WP_Error( 406, __( 'The specified "meta_key" had an invalid format.', 'wp-table-pixie' ) ) );
			} else {
				$model['meta_key'] = trim( $model['meta_key'] );
			}

			// Check and sanitize meta_value.
			if ( ! isset( $model['meta_value'] ) ) {
				$this->_ajax_return( new WP_Error( 406, __( 'A "meta_value" value was not specified.', 'wp-table-pixie' ) ) );
			}

			// Double quotes seem to always be escaped when coming from Backbone.
			$model['meta_value'] = stripslashes( $model['meta_value'] );

			// If broken serialized data, try to fix it.
			if ( is_serialized( $model['meta_value'] ) && Table_Pixie_Data_Format::is_broken_serialized( $model['meta_value'] ) ) {
				$meta_value = Table_Pixie_Admin_Edit::_maybe_fix_serialized_string( $model['meta_value'] );

				if ( $meta_value === $model['meta_value'] ) {
					$this->_ajax_return( new WP_Error( 500, __( 'The supplied broken serialized Value could not be fixed and saved.', 'wp-table-pixie' ) ) );
				}

				$model['meta_value'] = $meta_value;
			}

			// If value is to be base64 encoded and is expandable, do it!
			if ( ! empty( $model['base64'] ) && 'true' === $model['base64'] ) {
				if ( Table_Pixie_Data_Format::is_expandable( $model['meta_value'] ) ) {
					$model['meta_value'] = base64_encode( $model['meta_value'] );
				} else {
					$this->_ajax_return( new WP_Error( 406, __( 'Currently only expandable Values such as serialized data and JSON can be Base 64 encoded.', 'wp-table-pixie' ) ) );
				}
			}

			// If serialized data, un-serialize, so it can be properly serialized again on save.
			if ( is_serialized( $model['meta_value'] ) ) {
				$model['meta_value'] = Table_Pixie_Data_Format::maybe_unserialize( $model['meta_value'] );
			}

			if ( 'options' === $model['table'] ) {
				// Check and sanitize autoload.
				if ( empty( $model['autoload'] ) ) {
					$this->_ajax_return( new WP_Error( 406, __( 'An "autoload" value was not specified.', 'wp-table-pixie' ) ) );
				}
				if ( ! in_array( trim( $model['autoload'] ), array( 'yes', 'no' ) ) ) {
					$this->_ajax_return( new WP_Error( 406, __( 'The specified "autoload" was not either "yes" or "no".', 'wp-table-pixie' ) ) );
				} else {
					$model['autoload'] = trim( $model['autoload'] );
				}
			} else {
				// Check and sanitize related_id.
				if ( empty( $model['related_id'] ) ) {
					$this->_ajax_return( new WP_Error( 406, __( 'A "related_id" value was not specified.', 'wp-table-pixie' ) ) );
				}
				if ( sanitize_key( trim( $model['related_id'] ) ) !== trim( $model['related_id'] ) ) {
					$this->_ajax_return( new WP_Error( 406, __( 'The specified "related_id" had an invalid format.', 'wp-table-pixie' ) ) );
				} else {
					$model['related_id'] = trim( $model['related_id'] );
				}
			}
		}

		if ( 'read' === $action_type ) {
			$item = $this->_get_item( $model );
			if ( isset( $item->meta_value ) && ! empty( $item->meta_value ) && Table_Pixie_Data_Format::is_base64( $item->meta_value ) ) {
				$item->meta_value = base64_decode( $item->meta_value );
				$item->base64     = true;
			}
			$this->_ajax_return( $item );
		}

		if ( 'create' === $action_type ) {
			$model = $this->_save_item( $model );
			if ( ! is_wp_error( $model ) ) {
				$this->_add_notice( __( 'Record created.', 'wp-table-pixie' ) );
			}
			$this->_ajax_return( $model );
		}

		if ( 'update' === $action_type ) {
			$model = $this->_save_item( $model );
			if ( ! is_wp_error( $model ) ) {
				$this->_add_notice( __( 'Record updated.', 'wp-table-pixie' ) );
			}
			$this->_ajax_return( $model );
		}

		if ( 'delete' === $action_type ) {
			$result = $this->_delete_item( $model );
			if ( ! is_wp_error( $result ) ) {
				$this->_add_notice( __( 'Record deleted.', 'wp-table-pixie' ) );
			}
			$this->_ajax_return( $result );
		}
	}

	/**
	 * Return an item based on known item values.
	 *
	 * @param array $item
	 *
	 * @return mixed
	 */
	private function _get_item( $item ) {
		$params = array_intersect_key( $item, array(
			'blog_id'  => true,
			'table'    => true,
			'__tp_id'  => true,
			'meta_key' => true,
		) );

		return apply_filters( 'table_pixie_get_item', null, $params );
	}

	/**
	 * Save an item from array.
	 *
	 * @param array $item
	 *
	 * @return mixed|WP_Error
	 */
	private function _save_item( $item ) {
		$blog_id    = empty( $item['blog_id'] ) ? '' : sanitize_key( $item['blog_id'] );
		$table      = empty( $item['table'] ) ? '' : sanitize_key( $item['table'] );
		$__tp_id    = empty( $item['__tp_id'] ) ? '' : sanitize_key( $item['__tp_id'] );
		$meta_key   = empty( $item['meta_key'] ) ? '' : $item['meta_key'];
		$meta_value = empty( $item['meta_value'] ) ? '' : $item['meta_value'];
		$related_id = empty( $item['related_id'] ) ? '' : $item['related_id'];
		$autoload   = empty( $item['autoload'] ) ? '' : $item['autoload'];

		$meta_type = $this->_meta_type_for_table( $table );

		if ( is_numeric( $blog_id ) && is_multisite() ) {
			$blog_id = (int) $blog_id;
			switch_to_blog( $blog_id );
		}

		// For update, we need to remove and add so that related_id/autoload can be updated.
		// However, some records should not be deleted, and their meta_key and related_id/autoload should not be changed either.
		$add = true;
		if ( ! empty( $__tp_id ) ) {
			$old_item = $this->_get_item( array( 'blog_id' => $blog_id, 'table' => $table, '__tp_id' => $__tp_id ) );

			if ( ! empty( $old_item ) && $old_item->__tp_id === $__tp_id && ! $this->_meta_key_deletable( $table, $old_item->meta_key ) ) {
				$add = false;
				if ( $old_item->meta_key !== $meta_key ||
				     ( 'options' !== $table && $old_item->related_id !== $related_id ) ||
				     ( 'options' === $table && $old_item->autoload !== $autoload )
				) {
					return new WP_Error( 406, __( 'Only the value can be updated for this item.', 'wp-table-pixie' ) );
				}
				if ( empty( $meta_value ) ) {
					return new WP_Error( 406, __( 'A non blank value must be supplied for this item.', 'wp-table-pixie' ) );
				}
			} else {
				if ( ! $this->_delete_item( array(
					'blog_id' => $blog_id,
					'table'   => $table,
					'__tp_id' => $__tp_id,
				) )
				) {
					return new WP_Error( 500, __( 'Could not update item.', 'wp-table-pixie' ) );
				}
			}
		}

		if ( 'options' === $table && $add ) {
			$result = add_option( $meta_key, $meta_value, '', $autoload );
		} elseif ( 'options' === $table ) {
			$result = update_option( $meta_key, $meta_value );
		} elseif ( $add ) {
			$result = add_metadata( $meta_type, $related_id, $meta_key, $meta_value );
		} else {
			$result = update_metadata_by_mid( $meta_type, $__tp_id, $meta_value );
		}

		if ( is_numeric( $blog_id ) && is_multisite() ) {
			restore_current_blog();
		}

		if ( $result ) {
			return $this->_get_item( array( 'blog_id' => $blog_id, 'table' => $table, 'meta_key' => $meta_key ) );
		}

		// If here, we should be returning a WP_Error.
		if ( ! is_wp_error( $item ) ) {
			$item = new WP_Error( 500, __( 'Could not save item.', 'wp-table-pixie' ) );
		}

		return $item;
	}

	/**
	 * Delete an item.
	 *
	 * @param array $item
	 *
	 * @return bool
	 */
	private function _delete_item( $item ) {
		$blog_id = empty( $item['blog_id'] ) ? '' : sanitize_key( $item['blog_id'] );
		$table   = empty( $item['table'] ) ? '' : sanitize_key( $item['table'] );
		$__tp_id = empty( $item['__tp_id'] ) ? '' : sanitize_key( $item['__tp_id'] );

		$meta_type = $this->_meta_type_for_table( $table );

		$item = $this->_get_item( $item );

		$result = false;
		if ( ! empty( $item ) && $item->__tp_id === $__tp_id && $this->_meta_key_deletable( $table, $item->meta_key ) ) {
			if ( is_numeric( $blog_id ) && is_multisite() ) {
				$blog_id = (int) $blog_id;
				switch_to_blog( $blog_id );
			}

			if ( 'options' === $table ) {
				$result = delete_option( $item->meta_key );
			} else {
				$result = delete_metadata_by_mid( $meta_type, $__tp_id );
			}

			if ( is_numeric( $blog_id ) && is_multisite() ) {
				restore_current_blog();
			}
		}

		return $result;
	}

	/**
	 * Can we handle the given table name?
	 *
	 * @param string $table
	 *
	 * @return bool
	 */
	private function _valid_table( $table ) {
		$tables = array(
			'commentmeta',
			'options',
			'postmeta',
			'termmeta',
		);

		if ( is_multisite() ) {
			$tables[] = 'usermeta';
			$tables[] = 'sitemeta';
		}

		return in_array( $table, $tables );
	}

	/**
	 * Returns the metadata type (comment, post, site, term or user) for a table.
	 *
	 * @param string $table
	 *
	 * @return string
	 */
	private function _meta_type_for_table( $table ) {
		if ( $this->_valid_table( $table ) && 'options' !== $table ) {
			return substr( $table, 0, -4 );
		}

		return '';
	}
}
