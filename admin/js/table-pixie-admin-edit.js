var TablePixieAdminEdit = TablePixieAdminEdit || {};

(function( $, TablePixieAdminEdit ) {
	'use strict';

	/**
	 * A mixin for collections/models.
	 * Based on http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
	 */
	var AdminAjaxSyncableMixin = {
		url: ajaxurl,
		action: 'table_pixie_admin_edit_request',

		sync: function( method, object, options ) {
			if ( typeof options.data === 'undefined' ) {
				options.data = {};
			}

			options.data.nonce = TablePixieAdminEdit.nonce; // From localized script.
			options.data.action_type = method;

			// If no action defined, set default.
			if ( undefined === options.data.action && undefined !== this.action ) {
				options.data.action = this.action;
			}

			var json = this.toJSON();
			var formattedJSON = {};

			if ( json instanceof Array ) {
				formattedJSON.models = json;
			} else {
				formattedJSON.model = json;
			}

			_.extend( options.data, formattedJSON );

			// Need to use "application/x-www-form-urlencoded" MIME type.
			options.emulateJSON = true;

			// Force a POST with "create" method if not a read, otherwise admin-ajax.php does nothing.
			return Backbone.sync.call( this, 'create', object, options );
		}
	};

	/**
	 * A model for all your syncable models to extend.
	 * Based on http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
	 */
	var BaseModel = Backbone.Model.extend( _.defaults( {
		// parse: function( response ) {
		// Implement me depending on your response from admin-ajax.php!
		// return response;
		// }
	}, AdminAjaxSyncableMixin ) );

	/**
	 * A collection for all your syncable collections to extend.
	 * Based on http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
	 */
	var BaseCollection = Backbone.Collection.extend( _.defaults( {
		// parse: function( response ) {
		// 	Implement me depending on your response from admin-ajax.php!
		// return response;
		// }
	}, AdminAjaxSyncableMixin ) );

	/**
	 * Single Metadata record.
	 */
	TablePixieAdminEdit.MetadataModel = BaseModel.extend( {
		action: 'table_pixie_admin_edit_records',
		defaults: {
			error: null,
			blog_id: null,
			table: null,
			__tp_id: null,
			meta_key: null,
			meta_value: null,
			related_id: null,
			autoload: null,
			base64: false
		}
	} );

	/**
	 * Collection of metadata records.
	 */
	TablePixieAdminEdit.MetadataCollection = BaseCollection.extend( {
		action: 'table_pixie_admin_edit_records',
		model: TablePixieAdminEdit.MetadataModel
	} );

	/**
	 * A single metadata record's edit view.
	 */
	TablePixieAdminEdit.TablePixieAdminEditView = Backbone.View.extend( {
		el: '#table-pixie-admin-edit-modal .modal-dialog',
		template: _.template( $( '#table-pixie-admin-edit-tmpl' ).html() ),

		initialize: function() {
			this.listenTo( this.model, 'change', this.render );
			this.listenTo( this.model, 'destroy', this.remove );
			$( '#table-pixie-admin-edit-modal' ).on( 'shown.bs.modal', function() {
				$( '#table-pixie-admin-edit-meta_key' ).focus();
			} );
		},

		render: function() {
			var html = this.template( this.model.toJSON() );
			this.$el.html( html );

			return this;
		},

		events: {
			'click .button.button-primary': 'save'
		},

		save: function() {
			var changes = {
				error: null,
				meta_key: this.$( '#table-pixie-admin-edit-meta_key' ).val(),
				meta_value: this.$( '#table-pixie-admin-edit-meta_value' ).val(),
				related_id: this.$( '#table-pixie-admin-edit-related_id' ).val(),
				autoload: this.$( '#table-pixie-admin-edit-autoload' ).val(),
				base64: this.$( '#table-pixie-admin-edit-base64' ).prop( 'checked' )
			};
			this.model.save( changes, {
				success: function( model, response, options ) {
					window.location.href = location.href;
				},
				error: function( model, response, options ) {
					console.log( response );
					var error = TablePixieAdminEdit.strings.unknown_error;
					if ( undefined !== response.responseJSON.error && 0 < response.responseJSON.error.length ) {
						error = response.responseJSON.error;
					}
					model.set( 'error', error );
				}
			} );
		}
	} );

	TablePixieAdminEdit.confirmBulkAction = function( e ) {

		var action = $( this ).siblings( 'select[name="action"]' ).val();

		if ( 'table_pixie_admin_edit_delete' !== action ) {
			// No need to do anything when not deleting records.
			return true;
		}

		// Only show confirmation if something selected.
		if ( 0 < $( 'input:checkbox[name="record[]"]:checked' ).length && 'undefined' !== showNotice ) {
			return showNotice.warn();
		}

		return true;
	};

	TablePixieAdminEdit.showEdit = function( table, blog_id, __tp_id ) {
		if ( undefined == TablePixieAdminEdit.editView ) {
			TablePixieAdminEdit.record = new TablePixieAdminEdit.MetadataModel();
			TablePixieAdminEdit.editView = new TablePixieAdminEdit.TablePixieAdminEditView( { model: TablePixieAdminEdit.record } );
			TablePixieAdminEdit.editView.render();
		} else {
			// Clear existing model attributes without triggering events.
			TablePixieAdminEdit.record.clear( { silent: true } );
		}

		// Make sure attributes are back to defaults for add new or before fetch for edit.
		TablePixieAdminEdit.record.set( TablePixieAdminEdit.record.defaults );
		TablePixieAdminEdit.record.set( { blog_id: blog_id, table: table } );

		if ( null != __tp_id ) {
			TablePixieAdminEdit.record.set( { id: __tp_id, __tp_id: __tp_id } );
			TablePixieAdminEdit.record.fetch();
		}

		$( '#table-pixie-admin-edit-modal' ).modal( { backdrop: 'static' } );

		return false;
	};

	TablePixieAdminEdit.toggleDeleteProtected = function( event ) {
		event.preventDefault();
		var nonce = $( '#_table_pixie_nonce' ).val();
		var deleteProtected = $( this ).prop( "checked" );

		$.post(
			ajaxurl,
			{
				action: 'table_pixie_admin_edit_toggle_delete_protected',
				nonce: nonce,
				delete_protected: deleteProtected
			}
		)
	};

	$( document ).ready( function() {
		// Ask for confirmation before deleting records.
		$( 'body' ).on( 'click', 'input[type="submit"].button.action', TablePixieAdminEdit.confirmBulkAction );
		$( 'body' ).on( 'change', '#delete-protected', TablePixieAdminEdit.toggleDeleteProtected );
	} );

})( jQuery, TablePixieAdminEdit );
