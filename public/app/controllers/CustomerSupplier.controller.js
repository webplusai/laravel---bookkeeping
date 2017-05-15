'use strict';

function CustomerSupplierCtrl( $compile, $scope, $state, CRUDService, MiscService )
{
	$scope.person = {};
    $scope.personUIList = [];

	$scope.getList = function() {
		CRUDService.retrieve( $scope.targetTableName ).done(function(response) {
            $scope.personUIList = MiscService.extractUniqueList( response.data.content );
			CommonFunc().redrawDataTable( $scope.dataTable, response.data.content, $scope.generateRow, $scope.targetTableName );
            CommonFunc().hidePreloader( '.full-screen-loader' );
		} );
	}

    $scope.generateRow = function( row, i, tableName ) {
		return [ row.name, row.id, ( row.address1 ? row.address1 : '' ) + ' ' + ( row.address2 ? row.address2 : '' ) + ' ' + ( row.city ? row.city : '' ) + ' ' + ( row.country ? row.country : '' ), row.phone ? row.phone : '', row.email ? row.email : '', row.balance.toFixed( 2 ), CommonFunc().actionRow( row, tableName ) ];
	}

    $scope.goToNewInvoice = function( personName ) {
    	sessionStorage.personName = personName;
    	$state.go( 'app.main.sales.new-invoice' );
    }

    $scope.goToNewPayment = function( personName ) {
        sessionStorage.personName = personName;
        $state.go( 'app.main.sales.new-payment' );
    }

    $scope.goToNewSalesReceipt = function( personName ) {
        sessionStorage.personName = personName;
        $state.go( 'app.main.sales.new-sales-receipt' );
    }

    $scope.goToNewExpense = function( personName ) {
    	sessionStorage.personName = personName;
    	$state.go( 'app.main.expense.new-expense' );
    }

    $scope.initialize = function() {
        $scope.dataTable = CommonFunc().initializeDataTable( '#datatable', [ "Name", "ID", "Address", "Phone", "Email", "Balance", "Action"], $scope, $compile );
        $scope.getList();
    }

    setTimeout( function() {
        $scope.initialize();
    }, 0 );
}

angular
	.module( 'bookkeeping' )
	.controller( 'CustomerSupplierCtrl', [ '$compile', '$scope', '$state', 'CRUDService', 'MiscService', CustomerSupplierCtrl ] );