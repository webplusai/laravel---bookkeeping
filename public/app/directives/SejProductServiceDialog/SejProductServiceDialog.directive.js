'use strict';

function SejProductServiceDialog( CRUDService, MiscService ) {

	var output = {};

	output.templateUrl = 'app/directives/SejProductServiceDialog/SejProductServiceDialog.html';
	output.link = function( scope, element, attrs ) {

		scope.newProductService = { tableName: 'product_service', foreign_keys: [ 'product_category' ], is_active: 1 };

		scope.showCreateProductServiceDialog = function() {
			scope.isEditDialog = 0;
			$( "#" + attrs.id ).modal( 'show' );
			scope.productServiceDialogErrorMessages = [];
			scope.productService = JSON.parse( JSON.stringify( scope.newProductService ) );
		}

		scope.showEditProductServiceDialog = function( productServiceId ) {
			scope.isEditDialog = 1;
			CRUDService.retrieve( 'product_service', productServiceId ).done( function( response ) {
				scope.productService = response.data.content;
				scope.productService.product_category = scope.productCategoryUINames[ scope.productService.product_category_id ];
				scope.productService.tableName = 'product_service';
				scope.productService.foreign_keys = [ 'product_category' ];
				scope.productService.is_inventoriable = scope.productService.is_inventoriable == 1;
				scope.productServiceDialogErrorMessages = [];
				$( "#" + attrs.id ).modal( 'show' );
			});
		}

		scope.createProductService = function( bAddNew ) {
			CRUDService.create( scope.productService ).done( function( response ) {
				toastr.success( 'Successfully created' );
				scope.productServiceDialogErrorMessages = [];
				if ( scope.dataTable )
					CommonFunc().appendRowToDataTable( scope.dataTable, scope.generateProductServiceRow( response.data.content ) );
				else
					MiscService.massRetrieve( [ 'product_service' ] ).done( function( response ) {
						var content = response.data.content;
						scope.productServices = content.productService;
						scope.productServiceNames = MiscService.extractNameList( content.productService );
						scope.productServiceUINames = MiscService.extractUniqueNameList( content.productService );
					} );
				scope.productService = JSON.parse( JSON.stringify( scope.newProductService ) );
				if (bAddNew == false) {
					$( "#" + attrs.id ).modal( 'hide' );
				}
			} ).fail( function( response ) {
				scope.productServiceDialogErrorMessages = response.data.content;
			});
		}

		scope.updateProductService = function( id, data ) {
			if ( !id ) {
				id = scope.productService.id;
				data = scope.productService;
			}
			CRUDService.update( id, data ).done( function( response ) {
				toastr.success( 'Successfully updated' );
				if ( scope.dataTable )
					CommonFunc().redrawDataTable( scope.dataTable, response.data.content, scope.generateProductServiceRow );
				$( "#" + attrs.id ).modal( 'hide' );
			});
		}

		function initialize() {
			CommonFunc().initializeValidation( "form.form-horizontal.form-product-service", function( $form, errors ) {
				if ( scope.isEditDialog ) {
					scope.$eval( attrs.updateEvent )( scope.productService.id, scope.productService );
				} else {
					scope.$eval( attrs.createEvent )( scope.bAddNew );
				}
			} );
		}

		initialize();
	}
	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'sejProductServiceDialog', [ 'CRUDService', 'MiscService', SejProductServiceDialog ] );