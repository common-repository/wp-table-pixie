<!-- Record add/edit template. -->
<script type="text/template" id="table-pixie-admin-edit-tmpl">
	<div class="modal-content">
		<div class="modal-header">
			<% if ( null == __tp_id ) { %>
			<h4 class="modal-title" id="table-pixie-admin-edit-modal-title"><%- TablePixieAdminEdit.strings.add_record %></h4>
			<% } else { %>
			<h4 class="modal-title" id="table-pixie-admin-edit-modal-title"><%- TablePixieAdminEdit.strings.edit_record %></h4>
			<% } %>
		</div>
		<div class="modal-body">
			<% if ( null != error ) { %>
			<div class="error notice is-dismissible"><p><%- error %></p></div>
			<% } %>
			<label class="title" for="table-pixie-admin-edit-meta_key"><strong><?php _e( 'Key', 'wp-table-pixie' ); ?></strong></label>
			<input type="text" id="table-pixie-admin-edit-meta_key" name="meta_key" value="<%= meta_key %>" class="widefat"/>
			<label class="title" for="table-pixie-admin-edit-meta_value"><strong><?php _e( 'Value', 'wp-table-pixie' ); ?></strong></label>
			<textarea id="table-pixie-admin-edit-meta_value" name="meta_value" class="widefat" rows="10"><%= meta_value %></textarea>
			<% if ( 'options' === table ) { %>
			<label class="title" for="table-pixie-admin-edit-autoload"><strong><?php _e( 'Autoload', 'wp-table-pixie' ); ?></strong></label>
			<select id="table-pixie-admin-edit-autoload" name="autoload">
				<option value="no"
				<%= ( 'no' == autoload ) ? 'selected' : '' %>>no</option>
				<option value="yes"
				<%= ( 'yes' == autoload ) ? 'selected' : '' %>>yes</option>
			</select>
			<% } else { %>
			<label class="title" for="table-pixie-admin-edit-related_id"><strong><?php _e( 'Related ID', 'wp-table-pixie' ); ?></strong></label>
			<input type="number" id="table-pixie-admin-edit-related_id" name="related_id" value="<%= related_id %>" class="widefat"/>
			<% } %>
			<div id="table-pixie-admin-edit-base64-wrap">
				<label class="title" for="table-pixie-admin-edit-base64"><strong><?php _e( 'Base64 Encode', 'wp-table-pixie' ); ?></strong></label>
				<input type="checkbox" id="table-pixie-admin-edit-base64" name="base64" <%= ( true == base64 ) ? 'checked="checked"' : '' %> />
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="button button-default" data-dismiss="modal"><?php echo __( 'Cancel', 'wp-table-pixie' ); ?></button>
			<button type="button" class="button button-primary"><?php echo __( 'Save', 'wp-table-pixie' ); ?></button>
		</div>
	</div>
</script>

<!-- Metadata record add/edit modal. -->
<div class="modal fade" id="table-pixie-admin-edit-modal" tabindex="-1" role="dialog" aria-labelledby="table-pixie-admin-edit-modal-title">
	<div class="modal-dialog" role="document">
	</div>
</div>
