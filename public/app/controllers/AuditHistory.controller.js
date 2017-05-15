'use strict';

function AuditHistoryCtrl( $scope, $stateParams, MiscService ) {
	$scope.toggle = function( className, ele ) {
		if( $( "." + className ).css( "display" ) === "none" ) {
		  $( ele ).removeClass( 'icon-caret-up' ).addClass( 'icon-caret-down' )
		} else {
		  $( ele ).removeClass( 'icon-caret-down' ).addClass( 'icon-caret-up' )
		}
		$( "."+className ).slideToggle( "fast" );
	}

	$scope.initialize = function() {
		MiscService.massRetrieve( [ 'account', 'customer', 'supplier', 'product_service' ] ).done( function( response ) {
			var content = response.data.content;

			$scope.customerUIList = MiscService.extractUniqueList( content.customer );
			$scope.supplierUIList = MiscService.extractUniqueList( content.supplier );
			$scope.accountUINames = MiscService.extractUniqueNameList( content.account );
			$scope.productServiceUINames = MiscService.extractUniqueNameList( content.productService );

			MiscService.retrieveAuditHistory( $stateParams.trxnType, $stateParams.trxnId ).done( function( response ) {
				$scope.auditHistoryList = response.data.content;
			} );
		} );
	}

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'AuditHistoryCtrl', [ '$scope', '$stateParams', 'MiscService', AuditHistoryCtrl ] );