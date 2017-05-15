'use strict';

function SalesCtrl( $scope, $compile, $state, CRUDService, MiscService, TRXNService ) {

	$scope.customers = [];
	$scope.preloadCounter = 0;

	$scope.generateSalesRow = function( row ) {
		var transactionTypes = { '1': 'Invoice', '2': 'Payment', '3': 'Sales Receipt', '4': 'Credit Note' };
		var statues = [ [], [ '', 'Unpaid', 'Partial', 'Paid' ], [ '', 'Unapplied', 'Partial', 'Closed' ], [ '', 'Paid' ], [ '', 'Unapplied', 'Partial', 'Closed' ] ];
		var total = ( row.transaction_type != 2 && row.transaction_type != 4 )  ? row.total : -row.total;
		var balance = ( row.transaction_type != 2 && row.transaction_type != 4 ) ? row.balance: -row.balance;
		var action = statues[ row.transaction_type ][ row.status ];
		if ( row.transaction_type == 1 ) {
			if ( row.status == 1 )
				action = '<a href="" data-ng-click="goToPayment(' + row.invoice_receipt_no + ')"> Unpaid </a>';
			else if ( row.status == 2 )
				action = '<a href="" data-ng-click="goToPayment(' + row.invoice_receipt_no + ')"> Partial </a>';
		}
		return [ row.date, row.id, transactionTypes[ row.transaction_type ], row.invoice_receipt_no, $scope.customers[ row.customer_id ], row.transaction_type != 2 ? row.due_date : '', total.toFixed( 2 ), balance.toFixed( 2 ), action ];
	}

	$scope.goToPayment = function( invoiceId ) {
		$state.go( 'app.main.sales.new-payment', { invoiceId: invoiceId } );
	}

	$scope.initialize = function() {
		$scope.dataTable = CommonFunc().initializeDataTable( '#salesDataTable', [ "Date", "ID", "Type", "#", "Customer", "Due Date", "Total", "Balance", "Status" ], $scope, $compile );

		$( '#salesDataTable' ).on( 'click', 'tbody tr td:nth-child(9), tbody tr td:nth-child(1)', function( event ) {
			event.stopPropagation();
		} );

		$( '#salesDataTable' ).on( 'click', 'tbody tr', function() {
			CommonFunc().goToTransaction( $( this ).find( 'td:nth-child(2)' ).text(), $( this ).find( 'td:nth-child(3)' ).text().replace( ' ', '-' ).toLowerCase(), $state );
		} );

		MiscService.massRetrieve( [ 'customer', 'sales' ] ).done( function( response ) {
			var content = response.data.content;

			$scope.customers = MiscService.extractUniqueNameList( content.customer );
			CommonFunc().redrawDataTable( $scope.dataTable, content.sales, $scope.generateSalesRow, 'sales' );
			$scope.preloadCounter ++;
		} );

		$scope.$watch( function() {
			return $scope.preloadCounter;
		}, function( newVal, oldVal ) {
			if ( newVal == 1 )
				CommonFunc().hidePreloader( '.full-screen-loader' );
		} );
	}

	$scope.recoverDelete = function( trxnType, trxnId ) {
        alert();
        TRXNService.recoverDelete( trxnType, trxnId ).done( function( response ) {

        } );
    }

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'SalesCtrl', [ '$scope', '$compile', '$state', 'CRUDService', 'MiscService', 'TRXNService', SalesCtrl ] );