'use strict';

function AccountHistoryCtrl( $scope ) {

	$scope.generateAccountRow = function( row ) {
		return [ row.date, row.id, row.invoice_receipt_no ? row.invoice_receipt_no : '', $scope.accountDetailTypeUINames[ row.account_detail_type_id ], row.balance.toFixed( 2 ), CommonFunc().chartOfAccountActionRow( row, 'Account' ) ];
	}

	$scope.initialize = function() {
		$scope.dataTable = CommonFunc().initializeDataTable( '#datatable', [ "Date", "ID", "Ref No", "Type", "Payee", "Account", "Payment", "Deposit", "Balance" ], $scope, $compile );

		MiscService.massRetrieve( [ 'account', 'account_detail_type', 'account_category_type' ] ).done( function( response ) {
			var content = response.data.content;

			$scope.accounts = content.account;
			$scope.accountDetailTypeUINames = MiscService.extractUniqueNameList( content.accountDetailType );
			$scope.accountCategoryTypeNames = MiscService.extractNameList( content.accountCategoryType );
			$scope.accountCategoryTypeUINames = MiscService.extractUniqueNameList( content.accountCategoryType );

			$scope.account.account_category_type = $scope.accountCategoryTypeNames[ 0 ];
			$scope.getAccountDetailTypeNames();

			CommonFunc().redrawDataTable( $scope.dataTable, $scope.accounts, $scope.generateAccountRow );
			CommonFunc().hidePreloader( '.full-screen-loader' );
		} );
	}

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'AccountHistoryCtrl', [ '$scope', AccountHistoryCtrl ] );