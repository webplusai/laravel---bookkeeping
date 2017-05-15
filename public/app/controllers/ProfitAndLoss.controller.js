'use strict';

function ProfitAndLossCtrl( $scope, MiscService, RprtService ) {
	$scope.period = 'This year to date';
	$scope.report = {};
    $scope.companyName = sessionStorage.companyName;

    $scope.refineResult = function( report ) {

    	for ( var i = 1; i < report.costOfSalesServices.length; i++) {
			if ( report.costOfSalesServices[ i - 1 ].account_id == report.costOfSalesServices[ i ].account_id ) {
				report.costOfSalesServices[ i - 1 ].expense += report.costOfSalesServices[ i ].expense;
				report.costOfSalesServices.splice( i, 1 );
				i--;
			}
		}

		report.totalCostOfSales = 0;
		for ( var i = 0; i < report.costOfSalesServices.length; i++) {
            report.costOfSalesServices[i].accountName = $scope.accountUINames[ report.costOfSalesServices[i].account_id ];
			report.totalCostOfSales += report.costOfSalesServices[ i ].expense;
		}

		for ( var i = 1; i < report.expenses.length; i++) {
			if ( report.expenses[ i - 1 ].accountName == report.expenses[ i ].accountName ) {
				report.expenses[ i - 1 ].expense += report.expenses[ i ].expense;
				report.expenses.splice( i, 1 );
				i--;
			}
		}

        report.totalExpenses = 0;
        for ( var i = 0; i < report.expenses.length; i++ ) {
            report.expenses[ i ].accountName = $scope.accountUINames[ report.expenses[ i ].account_id ];
            report.totalExpenses += report.expenses[ i ].expense;
        }

    }

	$scope.retrieveReportByPeriod = function() {
        RprtService.retrieveReport( '/table/' + $scope.reportEndPoint, $scope.period ).then( function( response ) {
            $scope.report = response.data.content;
            $scope.refineResult( $scope.report );
        } );
    }

    $scope.retrieveReportByFromTo = function() {
        RprtService.retrieveReport( '/table/' + $scope.reportEndPoint, $scope.report.dateFrom, $scope.report.dateTo ).then( function( response ) {
            $scope.report = response.data.content;
            $scope.refineResult( $scope.report );
        } );
    }

    $scope.initialize = function() {
        CommonFunc().initializeDatePicker( '.dp-month-year', $scope );

        MiscService.massRetrieve( [ 'account' ] ).done( function( response ) {
            $scope.accountUINames = MiscService.extractUniqueNameList( response.data.content.account );
            setTimeout( function() {
                $scope.retrieveReportByPeriod();
            }, 10 );
        } );

        $scope.companyLogo = 'img/portrait/small/avatar-s-1.png';
        if ( sessionStorage.companyLogo != 'undefined' && sessionStorage.companyLogo != undefined ) {
            $scope.companyLogo = 'uploads/' + sessionStorage.companyLogo;
        }

        $scope.today = ( new Date() ).toString();
    }

    $scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'ProfitAndLossCtrl', [ '$scope', 'MiscService', 'RprtService', ProfitAndLossCtrl ] )