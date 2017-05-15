'use strict';

function BalanceSheetCtrl( $scope, RprtService ) {
	$scope.period = 'This year to date';
	$scope.report = {};
    $scope.companyName = sessionStorage.companyName;

	$scope.retrieveReportByPeriod = function() {
        RprtService.retrieveReport( '/table/' + $scope.reportEndPoint, $scope.period ).then( function( response ) {
            $scope.report = response.data.content;
        } );
    }

    $scope.retrieveReportByFromTo = function() {
        RprtService.retrieveReport( '/table/' + $scope.reportEndPoint, $scope.report.dateFrom, $scope.report.dateTo ).then( function( response ) {
            $scope.report = response.data.content;
        } );
    }

    $scope.initialize = function() {
        CommonFunc().initializeDatePicker( '.dp-month-year', $scope );

        setTimeout( function() {
        	$scope.retrieveReportByPeriod();
        }, 10 );

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
	.controller( 'BalanceSheetCtrl', [ '$scope', 'RprtService', BalanceSheetCtrl ] );