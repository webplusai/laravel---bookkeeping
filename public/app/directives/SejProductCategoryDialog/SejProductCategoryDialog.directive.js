'use strict';

function SejProductCategoryDialog( CRUDService, MiscService ) {

	var output = {};

	output.transclude = true;
	output.templateUrl = 'app/directives/SejProductCategoryDialog/SejProductCategoryDialog.html';
	output.link = function( scope, element, attrs ) {

		scope.showCreateProductCategoryDialog = function() {
			scope.isEditDialog = 0;
			$( "#" + attrs.id ).modal('show');
			scope.productCategory.name = '';
			scope.productCategoryDialogErrorMessages = [];
		}

		scope.showEditProductCategoryDialog = function( productCategoryId ) {
			scope.isEditDialog = 1;
			$( "#" + attrs.id ).modal( 'show' );
			scope.productCategoryDialogErrorMessages = [];
			CRUDService.retrieve( 'product_category', productCategoryId ).done( function( response ) {
				scope.productCategory = response.data.content;
				scope.productCategory.tableName = 'product_category';
			} );
		}

		scope.showDeleteDialog = function( productCategoryId ) {
			$( "#deleteConfirm" ).modal( 'show' );
			CRUDService.retrieve( 'product_category', productCategoryId ).done( function( response ) {
				scope.productCategory = response.data.content;
				scope.productCategory.tableName = 'product_category';
			} );
		}

		scope.createProductCategory = function() {
			CRUDService.create( scope.productCategory ).done( function( response ) {
				scope.productCategoryDialogErrorMessages = [];
				toastr.success( 'Successfully created' );
				if ( scope.dataTable && scope.generateProductCategoryRow )
					CommonFunc().appendRowToDataTable( scope.dataTable, scope.generateProductCategoryRow( response.data.content ) );
				else
					MiscService.massRetrieve( [ 'product_category' ] ).done( function( response ) {
						var content = response.data.content;
						scope.productCategoryNames = MiscService.extractNameList( content.productCategory );
						scope.productCategoryUINames = MiscService.extractUniqueNameList( content.productCategory );
					} );
				scope.productCategory.name = '';
				if ( scope.bAddNewCategory == false ) {
					$( "#" + attrs.id ).modal( 'hide' );
				}
			} ).fail( function( response ) {
				scope.productCategoryDialogErrorMessages = response.data.content;
			} );
		}

		scope.updateProductCategory = function() {
			CRUDService.update( scope.productCategory.id, scope.productCategory ).done( function( response ) {
				scope.productCategoryDialogErrorMessages = [];
				toastr.success( 'Successfully updated' );
				if ( scope.dataTable )
					CommonFunc().redrawDataTable( scope.dataTable, response.data.content, scope.generateProductCategoryRow );
				$( "#" + attrs.id ).modal( 'hide' );
			} );
		}

		scope.deleteProductCategory = function() {
			CRUDService.delete( scope.productCategory.id, scope.productCategory ).done( function( response ) {
				toastr.success( 'Successfully deleted' );
				CommonFunc().redrawDataTable( scope.dataTable, response.data.content, scope.generateProductCategoryRow );
				$( "#deleteConfirm" ).modal( 'hide' );
			} );
		}

		function initialize() {
			CommonFunc().initializeValidation( 'form.form-horizontal.form-product-category', function( $form, errors ) {
				if ( scope.isEditDialog )
					scope.$eval( attrs.updateEvent )( scope.productCategory.id, scope.productCategory );
				else
					scope.$eval( attrs.createEvent )();
			} );
		}

		initialize();
	}

	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'sejProductCategoryDialog', [ 'CRUDService', 'MiscService', SejProductCategoryDialog ] );