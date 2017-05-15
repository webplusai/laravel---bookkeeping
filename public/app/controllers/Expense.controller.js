'use strict';

function ExpenseCtrl( $scope, $state, $compile, CRUDService, MiscService, TRXNService ) {

	$scope.accounts = [];
	$scope.payees = [];
	$scope.customers = [];
	$scope.suppliers = [];

	$scope.pageName = 'New Transaction';

	$scope.setPayeeType = function( data, type ) {
		for ( var i = 0; i < data.length; i++ ) {
			data[ i ].type = type;
		}
		return data;
	}

	$scope.generateExpenseRow = function( row ) {
		var transaction_types = { '101': 'Expense', '102': 'Bill', '103': 'Bill Payment', '104': 'Supplier Credit' };
		var statuses = { '1': [ '', 'Unpaid', 'Partial', 'Paid' ], '2': [ '', 'Unapplied', 'Partial', 'Closed' ], '3': [ '', 'Paid' ], '101': [ '', 'Paid' ], '102': [ '', 'Unpaid', 'Partial', 'Paid' ], '103': [ '', 'Unapplied', 'Partial', 'Closed' ], '104': [ '', 'Unapplied', 'Partial', 'Closed' ] };
		var payee_id = row.payee_type == 1 ? row.payee_id : parseInt( row.payee_id ) + parseInt( $scope.customers.slice( -1 )[ 0 ].id ) + 1;
		var account = row.transaction_type != 103 ? ( row.account_id == 0 ? '- Split -' : $scope.accounts[ row.account_id ] ) :  $scope.accounts[ row.account_id ];
		var action = statuses[ row.transaction_type ][ row.status ];
		if ( row.transaction_type == 102 ) {
			if ( row.status == 1 )
				action = '<a href="" data-ng-click="goToBillPayment(' + row.id + ')"> Unpaid </a>';
			else if ( row.status == 2 )
				action = '<a href="" data-ng-click="goToBillPayment(' + row.id + ')"> Partial </a>';
		}
		return [ row.date, row.id, transaction_types[ row.transaction_type ], row.id, $scope.payees[ payee_id ].name, row.due_date, account, row.total.toFixed( 2 ), row.balance.toFixed( 2 ), action ];
	}

	$scope.goToBillPayment = function( expenseId ) {
		$state.go( 'app.main.expense.new-bill-payment', { expenseId: expenseId } );
	}

	$scope.recoverDelete = function( trxnType, trxnId ) {
        alert();
        TRXNService.recoverDelete( trxnType, trxnId ).done( function( response ) {

        } );
    }

	$scope.initialize = function() {
		$scope.dataTable = CommonFunc().initializeDataTable( '#expenseDataTable', [ "Data", "ID", "Type", "#", "Payee", "Due Date", "Category", "Total", "Balance", "Status" ], $scope, $compile );

		$( '#expenseDataTable' ).on( 'click', 'tbody tr td:nth-child(10), tbody tr td:nth-child(1)', function( event ) {
			event.stopPropagation();
		} );

		$( '#expenseDataTable' ).on( 'click', 'tbody tr', function() {
			CommonFunc().goToTransaction( $( this ).find( 'td:nth-child(2)' ).text(), $( this ).find( 'td:nth-child(3)' ).text().replace( ' ', '-' ).toLowerCase(), $state );
		} );

		MiscService.massRetrieve( [ 'account', 'customer', 'supplier', 'expenses' ] ).done( function( response ) {
			var content = response.data.content;

			$scope.accounts = MiscService.extractUniqueNameList( content.account );
			$scope.customers = $scope.setPayeeType( MiscService.extractUniqueList( content.customer ), 1 );
			$scope.suppliers = $scope.setPayeeType( MiscService.extractUniqueList( content.supplier ), 2 );
			$scope.payees = $scope.customers.concat( $scope.suppliers );
			$scope.expenses = content.expenses;

			CommonFunc().redrawDataTable( $scope.dataTable, $scope.expenses, $scope.generateExpenseRow, 'expense' );
			CommonFunc().hidePreloader( '.full-screen-loader' );
		} );
	}

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'ExpenseCtrl', [ '$scope', '$state', '$compile', 'CRUDService', 'MiscService', 'TRXNService', ExpenseCtrl ] );