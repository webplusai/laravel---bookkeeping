'use strict';

function ReportsCtrl( $scope, RprtService ) {

    $scope.profitLossPeriod = 'Last 30 days';

	$scope.drawProfitAndLossChart = function() {
        RprtService.retrieveReport( '/chart/profit_loss', $scope.profitLossPeriod ).then( function( response ) {
            var content = response.data.content;
            var profitLoss = [ [ 'Date', 'Income', 'Expense' ] ];

            $scope.income = 0;
            $scope.expense = 0;
            for ( var i = 0; i < content.length; i++ ) {
                $scope.income += content[i].profit;
                $scope.expense += content[i].loss;
                profitLoss.push( [ content[i].display, content[i].profit, content[i].loss ] );
            }

            $scope.netIncome = $scope.income - $scope.expense;

            var data = google.visualization.arrayToDataTable( profitLoss );
            CommonFunc().drawColumnChart( 'salesBarChart', data, CommonFunc().columnChartOptions );
        } );
    }

	$scope.initialize = function() {
        if ( typeof google != 'undefined' ) {
            google.load( 'visualization', '1.0', 
                {
                    callback: function() {
                        $scope.drawProfitAndLossChart();
                    }, 
                    packages:[ 'corechart' ]
                }
            );
        }
	}

	if ( typeof google !== 'undefined' && typeof google.visualization !== 'undefined' ) {
		$scope.drawProfitAndLossChart();
	}

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'ReportsCtrl', [ '$scope', 'RprtService', ReportsCtrl ] );