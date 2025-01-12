<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 */
class Table_Pixie {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 */
	protected $plugin_id;

	/**
	 * The current version of the plugin.
	 */
	protected $plugin_version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the Dashboard and
	 * the public-facing side of the site.
	 */
	public function __construct() {
		$this->plugin_id      = 'wp-table-pixie';
		$this->plugin_version = '1.2.2';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Table_Pixie_Loader. Orchestrates the hooks of the plugin.
	 * - Table_Pixie_i18n. Defines internationalization functionality.
	 * - Table_Pixie_Data_Format. Various data format routines.
	 * - Table_Pixie_List_Table. Extends WP_List_Table functionality.
	 * - Table_Pixie_Admin. Defines all hooks for the dashboard.
	 * - Table_Pixie_Admin_Edit. Defines all hooks for the dashboard edit functionality.
	 *
	 * Creates an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-table-pixie-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-table-pixie-i18n.php';

		/**
		 * The data format utilities class.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-table-pixie-data-format.php';

		/**
		 * The class responsible for creating and display of the list table.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-table-pixie-list-table.php';

		/**
		 * The class responsible for defining all actions that occur in the Dashboard.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-table-pixie-admin.php';

		if ( apply_filters( 'table_pixie_admin_edit_enabled', true ) ) {
			/**
			 * The class responsible for defining all edit actions that occur in the Dashboard.
			 */
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-table-pixie-admin-edit.php';
		}

		$this->loader = new Table_Pixie_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Table_Pixie_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 */
	private function set_locale() {
		$plugin_i18n = new Table_Pixie_i18n();
		$plugin_i18n->set_domain( $this->get_plugin_id() );

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all the hooks related to the dashboard functionality
	 * of the plugin.
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Table_Pixie_Admin( $this->get_plugin_id(), $this->get_plugin_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'init', $plugin_admin, 'delay_output' );

		if ( is_multisite() && is_network_admin() ) {
			$this->loader->add_action( 'network_admin_menu', $plugin_admin, 'add_menu_items' );
		} else {
			$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu_items' );
		}
		$this->loader->add_action( 'table_pixie_admin_page_hooked', $plugin_admin, 'admin_page_hooked' );
		$this->loader->add_filter( 'set-screen-option', $plugin_admin, 'set_records_per_page_option', 10, 3 );

		$this->loader->add_filter( 'table_pixie_get_query_string', $plugin_admin, 'get_query_string', 10, 2 );
		$this->loader->add_filter( 'table_pixie_get_count', $plugin_admin, 'get_count', 10, 1 );
		$this->loader->add_filter( 'table_pixie_get_item', $plugin_admin, 'get_item', 10, 2 );

		$this->loader->add_filter( 'table_pixie_column_display', $plugin_admin, 'column_display', 10, 3 );
		$this->loader->add_filter( 'table_pixie_column_row_actions', $plugin_admin, 'column_row_actions', 10, 3 );
		$this->loader->add_filter( 'table_pixie_extra_tablenav', $plugin_admin, 'extra_tablenav', 10, 2 );
		$this->loader->add_action( 'wp_ajax_table_pixie_toggle_truncate', $plugin_admin, 'ajax_toggle_truncate' );
		$this->loader->add_action( 'wp_ajax_table_pixie_toggle_remember_search', $plugin_admin, 'ajax_toggle_remember_search' );
		$this->loader->add_action( 'added_user_meta', $plugin_admin, 'update_columns_hidden', 10, 4 );
		$this->loader->add_action( 'updated_user_meta', $plugin_admin, 'update_columns_hidden', 10, 4 );

		// We just want to access some static functions.
		$plugin_list_table = 'Table_Pixie_List_Table';

		$this->loader->add_filter( 'table_pixie_format_row_actions', $plugin_list_table, 'format_row_actions' );

		if ( apply_filters( 'table_pixie_admin_edit_enabled', true ) ) {
			$plugin_admin_edit = new Table_Pixie_Admin_Edit( $this->get_plugin_id(), $this->get_plugin_version() );

			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin_edit, 'enqueue_styles' );
			$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin_edit, 'enqueue_scripts' );
			$this->loader->add_action( 'admin_footer', $plugin_admin_edit, 'add_templates' );
			$this->loader->add_action( 'admin_notices', $plugin_admin_edit, 'admin_notices' );
			$this->loader->add_action( 'network_admin_notices', $plugin_admin_edit, 'admin_notices' );

			$this->loader->add_action( 'table_pixie_admin_page_hooked', $plugin_admin_edit, 'admin_page_hooked' );
			$this->loader->add_filter( 'table_pixie_admin_title', $plugin_admin_edit, 'admin_title' );
			$this->loader->add_filter( 'table_pixie_set_help_tabs', $plugin_admin_edit, 'set_help_tabs' );

			$this->loader->add_filter( 'table_pixie_get_bulk_actions', $plugin_admin_edit, 'get_bulk_actions' );
			$this->loader->add_action( 'table_pixie_process_action', $plugin_admin_edit, 'process_action', 10, 5 );
			$this->loader->add_filter( 'table_pixie_column_row_actions', $plugin_admin_edit, 'column_row_actions', 10, 3 );

			$this->loader->add_filter( 'wp_ajax_table_pixie_admin_edit_records', $plugin_admin_edit, 'ajax_records' );
			$this->loader->add_action( 'wp_ajax_table_pixie_admin_edit_toggle_delete_protected', $plugin_admin_edit, 'ajax_toggle_delete_protected' );
		}
	}

	/**
	 * Run the loader to execute all the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return string The name of the plugin.
	 */
	public function get_plugin_id() {
		return $this->plugin_id;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return Table_Pixie_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return string The version number of the plugin.
	 */
	public function get_plugin_version() {
		return $this->plugin_version;
	}
}
