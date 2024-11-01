<?php

/**
 * @wordpress-plugin
 * Plugin Name:       WP Table Pixie
 * Plugin URI:        https://wordpress.org/plugins/wp-table-pixie/
 * Description:       Search, sort, view and edit your settings and metadata, even serialized and base64 encoded values.
 * Version:           1.2.2
 * Author:            Ian M. Jones
 * Author URI:        https://ianmjones.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-table-pixie
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-table-pixie-activator.php
 */
function table_pixie_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-table-pixie-activator.php';
	Table_Pixie_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-table-pixie-deactivator.php
 */
function table_pixie_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-table-pixie-deactivator.php';
	Table_Pixie_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'table_pixie_activate' );
register_deactivation_hook( __FILE__, 'table_pixie_deactivate' );

/**
 * Returns a URL for asking for support.
 *
 * @return string
 */
function table_pixie_support_url() {
	return apply_filters( 'table_pixie_support_url', 'https://wordpress.org/support/plugin/wp-table-pixie/' );
}

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-table-pixie.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 */
function table_pixie_run() {
	$plugin = new Table_Pixie();
	$plugin->run();
}

table_pixie_run();
