'use strict';

function ChartOfAccountCtrl( $scope, $state, $compile, CRUDService, MiscService ) {

	$scope.isMassEditMode = 0;
	$scope.deleteAccountId = 0;

	$scope.accounts 	=	[];

	$scope.generateAccountRow = function( row ) {
		return [ row.name, row.id, $scope.accountCategoryTypeUINames[ row.account_category_type_id ], $scope.accountDetailTypeUINames[ row.account_detail_type_id ], row.balance.toFixed( 2 ), CommonFunc().chartOfAccountActionRow( row, 'Account' ) ];
	}

	$scope.generateEditableAccountRow = function( row, index ) {
		return [ '<div style="display: none;"> ' + row.name + ' </div><input value="' + row.name + '" style="padding: 8px 4px;" data-ng-model="accounts[' + index + '].name">', row.id, $scope.accountCategoryTypeUINames[ row.account_category_type_id ], $scope.accountDetailTypeUINames[ row.account_detail_type_id ], row.balance.toFixed( 2 ), CommonFunc().editDeleteActionRow( row, 'Account' ) ];
	}

	$scope.goToAccountHistory = function( accountId ) {
		$state.go( 'app.main.other.account-history', { accountId: accountId } );
	}

	$scope.enableMassEditMode = function() {
		$scope.isMassEditMode = 1;
		CommonFunc().redrawDataTable( $scope.dataTable, $scope.accounts, $scope.generateEditableAccountRow );
	}

	$scope.cancelMassEditMode = function() {
		$scope.isMassEditMode = 0;
		CommonFunc().redrawDataTable($scope.dataTable, $scope.accounts, $scope.generateAccountRow);
	}

	$scope.massUpdate = function() {
		MiscService.massUpdate( { data: $scope.accounts } ).done( function( response ) {
			$scope.isMassEditMode = 0;
			toastr.success( 'Successfully updated' );
			CommonFunc().redrawDataTable( $scope.dataTable, response.data.content, $scope.generateAccountRow );
		} ).fail( function( response ) {

		} );
	}

	$scope.initialize = function() {
		$scope.dataTable = CommonFunc().initializeDataTable( '#datatable', [ "Name", "ID", "Type", "Detail Type", "Balance", "Action" ], $scope, $compile );

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
	.controller( 'ChartOfAccountCtrl', [ '$scope', '$state', '$compile', 'CRUDService', 'MiscService', ChartOfAccountCtrl ] );