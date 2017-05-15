'use strict';

function CompanySnapshotCtrl( $scope, $compile, MiscService, RprtService ) {

	$scope.incomePeriod = 'This year to date';
	$scope.expensePeriod = 'This year to date';
	$scope.incomeComparisonPeriod = 'Monthly';
	$scope.expenseComparisonPeriod = 'Monthly';
	$scope.incomeAccount = 'All accounts';
	$scope.expenseAccount = 'All accounts';

	$scope.incomeTotal = 0;
	$scope.expenseTotal = 0;

	$scope.preloadCounter = 0;

	$scope.drawIncomeChart = function() {
		RprtService.retrieveReport( '/chart/income_circle', $scope.incomePeriod ).then( function( response ) {
			var content = response.data.content;
			var income = [ [ 'Source', 'Income' ] ];

			$scope.incomeTotal = 0;

			for ( var i = 1; i < content.length; i++) {
				if ( content[ i - 1 ].product_service_id == content[ i ].product_service_id ) {
					content[ i - 1 ].income += content[ i ].income;
					content.splice( i, 1 );
					i--;
				}
			}

			for ( var i = 0; i < content.length; i++ ) {
				income.push( [ '$' + content[i].income + ' ' + $scope.productServiceNames[ content[ i ].product_service_id ], content[ i ].income ] );
				$scope.incomeTotal += content[ i ].income;
			}

			var data = google.visualization.arrayToDataTable( income );
			CommonFunc().drawPieChart( 'income-chart', data, CommonFunc().pieChartOptions_Snapshot );
			$scope.preloadCounter ++;
		} );
	}

	$scope.drawExpenseChart = function() {
		RprtService.retrieveReport( '/chart/expense/dashboard', $scope.expensePeriod ).then( function( response ) {
			var content = response.data.content;
			var expense = [ [ 'Source', 'Expense' ] ];

			$scope.expenseTotal = 0;

			for ( var i = 1; i < content.length; i++) {
				if ( content[ i - 1 ].account_id == content[ i ].account_id ) {
					content[ i - 1 ].expense += content[ i ].expense;
					content.splice( i, 1 );
					i--;
				}
			}
			
			for ( var i = 0; i < content.length; i++ ) {
				expense.push( [ $scope.accountNames[ content[ i ].account_id ], content[ i ].expense ] );
				$scope.expenseTotal += content[ i ].expense;
			}
			var data = google.visualization.arrayToDataTable( expense );
			CommonFunc().drawPieChart( 'expense-chart', data, CommonFunc().pieChartOptions_Snapshot );
			$scope.preloadCounter ++;
		} );
	}

	$scope.drawIncomeComparisonChart = function() {
		RprtService.retrieveReport( '/chart/income_comparison', $scope.incomeComparisonPeriod, $scope.incomeAccount ).then( function( response ) {
			var content = response.data.content;
			var incomeComparison = [ [ 'Income', 'This year', 'Last year' ] ];

			for ( var i = 0; i < content.length; i++ ) {
				incomeComparison.push( [ content[ i ].display, content[ i ].this_year, content[ i ].last_year ] );
			}

			var data = google.visualization.arrayToDataTable( incomeComparison );
			CommonFunc().drawColumnChart( 'income_comparison_chart', data, CommonFunc().columnChartOptions );
			$scope.preloadCounter ++;
		} );
	}

	$scope.drawExpenseComparisonChart = function() {
		RprtService.retrieveReport( '/chart/expense_comparison', $scope.expenseComparisonPeriod, $scope.expenseAccount ).then( function( response ) {
			var content = response.data.content;
			var expenseComparison = [ [ 'Expense', 'This year', 'Last year' ] ];

			for ( var i = 0; i < content.length; i++ ) {
				expenseComparison.push( [ content[ i ].display, content[ i ].this_year, content[ i ].last_year ] );
			}
			
			var data = google.visualization.arrayToDataTable( expenseComparison );
			CommonFunc().drawComboChart( 'expense_comparison_chart', data, CommonFunc().comboChartOptions );
			$scope.preloadCounter ++;
		} );
	}

	$scope.drawOweMeTable = function() {
		RprtService.retrieveReport( '/table/who_owes_me' ).then( function( response ) {
			var content = response.data.content;
			for ( var i = 0; i < content.length; i++ ) {
				content[ i ].name = $scope.customers[ content[ i ].payee_id ].name;
			}
			CommonFunc().redrawDataTable( $scope.tableOweMe, content, $scope.generateDataTableRow );
			$scope.preloadCounter ++;
		} );
	}

	$scope.generateDataTableRow = function( row ) {
		return [ row.name, 0, row.amount.toFixed( 2 ) ];
	}

	$scope.drawIOweTable = function() {
		RprtService.retrieveReport( '/table/whom_i_owe' ).then( function( response ) {
			var content = response.data.content;
			for ( var i = 0; i < content.length; i++ ) {
				if ( content[ i ].payee_type == 1 ) {
					content[ i ].name = $scope.customers[ content[ i ].payee_id ].name;
				} else if ( content[ i ].payee_type == 2 ) {
					content[ i ].name = $scope.suppliers[ content[ i ].payee_id ].name;
				}
			}
			CommonFunc().redrawDataTable( $scope.tableIOwe, content, $scope.generateDataTableRow );
			$scope.preloadCounter ++;
		} );
	}

	$scope.drawSnapshotCharts = function() {
		MiscService.massRetrieve( [ 'product_service' ] ).done( function( response ) {
			$scope.productServices = MiscService.extractNameList( response.data.content.productService );
			$scope.drawIncomeChart();
			$scope.drawExpenseChart();
		} );
		$scope.drawIncomeComparisonChart();
		$scope.drawExpenseComparisonChart();
	}

	$scope.initialize = function() {
		google.load('visualization', '1.0', {
			callback: function() {
				MiscService.massRetrieve( [ 'customer', 'supplier', 'account', 'product_service' ] ).done( function( response ) { 
					var content = response.data.content;

					$scope.customers = MiscService.extractUniqueList( content.customer );
					$scope.suppliers = MiscService.extractUniqueList( content.supplier );
					$scope.accountNames = MiscService.extractUniqueNameList( content.account );
					$scope.productServiceNames = MiscService.extractUniqueNameList( content.productService );

					$scope.drawOweMeTable();
					$scope.drawIOweTable();
					$scope.drawIncomeChart();
					$scope.drawExpenseChart();
					$scope.drawIncomeComparisonChart();
					$scope.drawExpenseComparisonChart();
				} );
			},
			packages: [ 'corechart' ]
		});

		$scope.tableIOwe = CommonFunc().initializeDataTable( '#datatable_i_owe', [ 'Customer', 'ID', 'Amount' ], $scope, $compile );
		$scope.tableOweMe = CommonFunc().initializeDataTable( '#datatable_owe_me', [ 'Customer', 'ID', 'Amount' ], $scope, $compile );

		CommonFunc().setPreloadWatcher( $scope, 6 );
	}

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'CompanySnapshotCtrl', [ '$scope', '$compile', 'MiscService', 'RprtService', CompanySnapshotCtrl ] ); 