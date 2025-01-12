<?php

/**
 * Provide a dashboard view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 */

global $wp_version;
$header_tag = 'h1';
if ( version_compare( $wp_version, '4.3-dev', '<' ) ) {
	$header_tag = 'h2';
}
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<?php
	echo "<$header_tag>";
	echo apply_filters( 'table_pixie_admin_title', __( 'WP Table Pixie', 'wp-table-pixie' ) );
	if ( ! empty( $_REQUEST['s'] ) ) {
		printf( ' <span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_attr( $_REQUEST['s'] ) );
	}
	echo "</$header_tag>";
	?>

	<?php $table_pixie_list_table->views(); ?>

	<form id="table-pixie-form" method="post">
		<?php
		if ( ! empty( $_REQUEST['_table_pixie_nonce'] ) ) {
			?>
			<input type="hidden" name="_table_pixie_nonce" id="_table_pixie_nonce" value="<?php echo esc_attr( $_REQUEST['_table_pixie_nonce'] ) ?>" />
			<?php
		}
		if ( ! empty( $_REQUEST['blog_id'] ) ) {
			?>
			<input type="hidden" name="current_blog_id" id="current-blog-id" value="<?php echo esc_attr( $_REQUEST['blog_id'] ) ?>" />
			<?php
		}
		if ( ! empty( $_REQUEST['table'] ) ) {
			?>
			<input type="hidden" name="current_table" id="current-table" value="<?php echo esc_attr( $_REQUEST['table'] ) ?>" />
			<?php
		}
		if ( ! empty( $_REQUEST['record_type'] ) ) {
			?>
			<input type="hidden" name="record_type" value="<?php echo esc_attr( $_REQUEST['record_type'] ) ?>" />
			<?php
		}
		if ( ! empty( $_REQUEST['page'] ) ) {
			?>
			<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ) ?>" />
			<?php
		}
		$table_pixie_list_table->search_box( __( 'Search', 'wp-table-pixie' ), 'option' );
		$table_pixie_list_table->display();
		?>
	</form>
	<?php echo apply_filters( 'table_pixie_admin_page_footer', '' ); ?>
</div>
