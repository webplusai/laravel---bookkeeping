'use strict';

function AuditLogCtrl( $scope, $stateParams, $compile, MiscService ) {

	$scope.showCustomerSupplierEditDialog = function( tableId, id ) {
		$scope.targetTableName = tableId == 2 ? 'customer' : ( tableId == 3 ? 'supplier' : '' );
		$scope.showEditPersonDialog( id );
	}

	$scope.modifyData = function( data ) {

		var json = JSON.parse( data );
		var data = json.aaData;
		var personTypes = { 'Customer': 1, 'Supplier': 2 };
		var tableIds = { 'User': 1, 'Customer': 2, 'Supplier': 3, 'Company Profile': 4, 'Account': 5, 'Product Service': 6, 'Journal Entry': 7, 'Product Category': 8, 'User Profile': 9, 'Sales': 10, 'Expenses': 11, 'Attachment': 12 };
		var tableNames = { 1: 'User', 2: 'Person', 3: 'Person', 4: 'Company Profile', 5: 'Account', 6: 'Product Service', 7: 'Journal Entry', 8: 'Product Category', 9: 'User Profile', 10: 'Sales', 11: 'Expenses', 12: 'Attachment' };

		for ( var i = 0; i < data.length; i++ ) {
			if ( data[i][0] == tableIds[ 'User' ] ) {
				data[i][6] = 'Logged in';
			} else if ( data[i][0] == tableIds[ 'Sales' ] || data[i][0] == tableIds[ 'Expenses' ] ) {
				data[i][6] += ' <a href="#/' + data[i][7].replace( ' ', '-' ).toLowerCase() + '?trxnId=' + data[i][1] + '">' + data[i][7] + ' No.' + data[i][2] + '</a>';
			} else {
				if ( data[i][0] == tableIds[ 'Journal Entry' ] ) {
					data[i][6] += ': <a href="#/new-journal-entry?trxnId=' + data[i][1] + '">' + data[i][7] + '</a>';
				} else if ( data[i][0] == tableIds[ 'Company Profile' ] || data[i][0] == tableIds[ 'User Profile' ] ) {
					data[i][6] += ': <a href="#/' + data[i][7].replace( ' ', '-' ).toLowerCase() + '">' + data[i][7] + '</a>';
				} else if ( data[i][0] == tableIds[ 'Customer' ] || data[i][0] == tableIds[ 'Supplier' ] ) {
					data[i][6] += ': <a href="" data-ng-click="showCustomerSupplierEditDialog(' + data[i][0] + ',' + data[i][1] + ')">' + data[i][7] + '</a>';
				} else {
					data[i][6] += ': <a href="" data-ng-click="showEdit' + tableNames[ data[i][0] ].replace( ' ', '' ) + 'Dialog(' + data[i][1] + ')">' + data[i][7] + '</a>';
				}
			}


			if ( data[i][9] == personTypes[ 'Customer' ] ) {
				data[i][8] = $scope.customerUIList[ data[i][8] ].name;
			} else if ( data[i][9] == personTypes[ 'Supplier' ] ) {
				data[i][8] = $scope.supplierUIList[ data[i][8] ].name;
			}

			data[i].splice( 9, 1 );
			data[i].splice( 7, 1 );

			if ( !$stateParams.tableId && !$stateParams.recordId ) {
				if ( data[i][0] == tableIds[ 'User' ] )
					data[i].push( '' );
				else if ( data[i][0] == tableIds[ 'Sales' ] || data[i][0] == tableIds[ 'Expenses' ] )
					data[i].push( '<a href="#/audit-history?trxnType=' + data[i][0] + '&trxnId=' + data[i][1] + '"> View </a>' );
				else
					data[i].push( '<a href="#/audit-log?tableId=' + data[i][0] + '&recordId=' + data[i][1] + '"> View </a>' );
			}

			data[i].splice( 0, 3 );
		}
				
		return JSON.stringify( json );
	}

	$scope.initialize = function() {
		if ( $stateParams.tableId && $stateParams.recordId ) {
			$scope.auditLogTable = CommonFunc().initializeDataTable( '#auditLogDataTable', [ "Date changed", "ID", "User", "Event", "Name", "Date", "Amount" ], $scope, $compile, true, '/misc/retrieve_audit_log?endPointId=retrieveAuditLog&tableId=' + $stateParams.tableId + '&recordId=' + $stateParams.recordId, $scope.modifyData );
		} else {
			$scope.auditLogTable = CommonFunc().initializeDataTable( '#auditLogDataTable', [ "Date changed", "ID", "User", "Event", "Name", "Date", "Amount", "History" ], $scope, $compile, true, '/misc/retrieve_audit_log?endPointId=retrieveAuditLog', $scope.modifyData );
		}
		MiscService.massRetrieve( [ 'customer', 'supplier', 'account', 'product_category', 'product_service' ] ).done( function( response ) {
			var content = response.data.content;

			$scope.customerUIList = MiscService.extractUniqueList( content.customer );
			$scope.supplierUIList = MiscService.extractUniqueList( content.supplier );
			$scope.accountUINames = MiscService.extractUniqueNameList( content.account );
			$scope.productCategoryNames = MiscService.extractNameList( content.productCategory );
			$scope.productCategoryUINames = MiscService.extractUniqueNameList( content.productCategory );
			$scope.productServiceUInames = MiscService.extractUniqueNameList( content.productService );

			$scope.auditLogTable.draw();
		} );
	}

	$scope.initialize();

}

angular
	.module( 'bookkeeping' )
	.controller( 'AuditLogCtrl', [ '$scope', '$stateParams', '$compile', 'MiscService', AuditLogCtrl ] );