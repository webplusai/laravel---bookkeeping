'use strict';

function SejAccountDialog( CRUDService, MiscService ) {

	var output = {};

	output.transclude = true;
	output.templateUrl = 'app/directives/SejAccountDialog/SejAccountDialog.html';
	output.link = function( scope, element, attrs ) {

		scope.account 						=	{};
		scope.newAccount 					=	{ tableName: 'account', foreign_keys: ['account_category_type', 'account_detail_type'], balance: 0 };

		scope.accountNames 					=	[];

		scope.accountCategoryTypeNames 		= 	[];
		scope.accountDetailTypeNames 		= 	[];

		scope.accountCategoryTypeUINames	=	[];
		scope.accountDetailTypeUINames 		=	[];

		scope.getAccountDetailTypeNames = function() {
			MiscService.retrieveAccountDetailTypeNames( scope.account.account_category_type ).done( function( data ) {
				scope.accountDetailTypeNames = data;
				scope.account.account_detail_type = data[0];
			} );
		}

		scope.showCreateAccountDialog = function() {
			scope.isEditDialog = 0;
			scope.accountDialogErrorMessages = [];
			scope.account = CommonFunc().cloneObject( scope.newAccount );
			$( '#' + attrs.id ).modal( 'show' );
		}

		scope.showEditAccountDialog = function( accountId ) {
			CRUDService.retrieve( 'account', accountId ).done( function( response ) {
				scope.account = response.data.content;
				scope.account.account_category_type = scope.accountCategoryTypeUINames[ scope.account.account_category_type_id ];
				scope.account.tableName = 'account';
				MiscService.retrieveAccountDetailTypeNames( scope.account.account_category_type ).done( function( data ) {
					scope.isEditDialog = 1;
					scope.accountDetailTypeNames = data;
					scope.accountDialogErrorMessages = [];
					scope.account.account_detail_type = scope.accountDetailTypeUINames[ scope.account.account_detail_type_id ];
					$( '#' + attrs.id ).modal( 'show' );
				});
			});
		}

		scope.showDeleteDialog = function( accountId ) {
			CRUDService.retrieve( 'account', accountId ).done( function( response ) {
				scope.account = response.data.content;
				scope.account.tableName = 'account';
			} );
			$( "#deleteConfirm" ).modal( 'show' );
		}

		scope.createAccount = function( bAddNew ) {
			CRUDService.create( scope.account ).done( function( response ) {
				scope.accountDialogErrorMessages = [];
				toastr.success( "Successfully created" );
				if ( scope.dataTable )
					CommonFunc().appendRowToDataTable( scope.dataTable, scope.generateAccountRow( response.data.content ) );
				else
					MiscService.massRetrieve( [ 'account' ] ).done( function( response ) {
						scope.accountNames = MiscService.extractNameList( response.data.content.account );
					} );

				if ( !bAddNew ) {
					$( '#' + attrs.id ).modal( 'hide' );
				}
				scope.account = CommonFunc().cloneObject( scope.newAccount );
			} ).fail( function( response ) {
				scope.accountDialogErrorMessages = response.data.content;
			} );
		}

		scope.updateAccount = function( id, data ) {
			CRUDService.update( id, data ).done( function( response ) {
				$( '#' + attrs.id ).modal( 'hide' );
				toastr.success( 'Successfully updated' );
				CommonFunc().redrawDataTable( scope.dataTable, response.data.content, scope.generateAccountRow );
			} );
		}

		scope.deleteAccount = function() {
			CRUDService.delete( scope.account.id, scope.account ).done( function( response ) {
				$( "#deleteConfirm" ).modal( 'hide' );
				toastr.success( 'Succesfully deleted' );
				CommonFunc().redrawDataTable( scope.dataTable, response.data.content, scope.generateAccountRow );
			});
		}

		function initialize() {

			CommonFunc().initializeValidation( 'form.form-horizontal.form-account', function( $form, errors ) {
				if ( scope.isEditDialog ) {
					scope.$eval( attrs.updateEvent )( scope.account.id, scope.account );
				} else {
					scope.$eval( attrs.createEvent )( scope.bAddNew );
				}
			} );

			scope.account = CommonFunc().cloneObject( scope.newAccount );
		}

		initialize();
	}

	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'sejAccountDialog', [ 'CRUDService', 'MiscService', SejAccountDialog ] );