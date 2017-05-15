'use strict';

function ProductCategoryCtrl( $scope, $compile, CRUDService ) {

	$scope.isEditDialog = 0;
	$scope.productCategory = {};

	$scope.getProductCategoryList = function() {
		CRUDService.retrieve( 'product_category' ).done( function( response ) {
			CommonFunc().redrawDataTable( $scope.dataTable, response.data.content, $scope.generateProductCategoryRow );
		} );
	}

	$scope.generateProductCategoryRow = function( row ) {
		return [ row.name, row.id, CommonFunc().editDeleteActionRow( row, 'ProductCategory' ) ];
	}

	$scope.initialize = function() {
		$scope.dataTable = CommonFunc().initializeDataTable( '#datatable', [ "Name", "ID", "Action" ], $scope, $compile );
		$scope.getProductCategoryList();
	}

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'ProductCategoryCtrl', [ '$scope', '$compile', 'CRUDService', ProductCategoryCtrl ] );