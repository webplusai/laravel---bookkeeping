'use strict';

function ExpenseBySupplierSummaryCtrl( $scope, RprtService, CRUDService, MiscService ) {

    $scope.summaryList = [];
    $scope.customers = []
    $scope.payees = [];
    $scope.total = 0;
    $scope.period = 'This year to date';
    $scope.companyName = sessionStorage.companyName;

    $scope.preloadCounter = 0;

    $scope.processSummaryList = function() {
        $scope.total = 0;
        for ( var i = 0; i < $scope.summaryList.length; i++ ) {
            var payeeId = $scope.summaryList[ i ].payee_id;
            if ( $scope.summaryList[ i ].payee_type == 2 )
                payeeId += $scope.customers.length;
            $scope.summaryList[ i ].payee_name = $scope.payees[ payeeId ];

            $scope.total += $scope.summaryList[ i ].total;
        }
    }

    $scope.retrieveSummaryListByPeriod = function() {
        RprtService.retrieveReport( '/table/expense_by_supplier', $scope.period ).then( function( response ) {
            $scope.summaryList = response.data.content.report;
            $scope.dateFrom = response.data.content.dateFrom;
            $scope.dateTo = response.data.content.dateTo;
            $scope.processSummaryList();

            CommonFunc().hidePreloader( '.full-screen-loadser' );
        } );
    }

    $scope.retrieveSummaryListByFromTo = function() {
        RprtService.retrieveReport( '/table/expense_by_supplier', $scope.dateFrom, $scope.dateTo ).then( function( response ) {
            $scope.summaryList = response.data.content;
            $scope.processSummaryList();
        } );
    }

    $scope.initialize = function() {
        CommonFunc().initializeDatePicker( '.dp-month-year', $scope );

        MiscService.massRetrieve( [ 'customer', 'supplier' ] ).done( function( response ) {
            var content = response.data.content;

            $scope.customers = MiscService.extractUniqueNameList( content.customer );
            $scope.suppliers = MiscService.extractUniqueNameList( content.supplier );
            $scope.payees = $scope.customers.concat( $scope.suppliers );
            $scope.retrieveSummaryListByPeriod();
        } );

        $scope.companyLogo = 'img/portrait/small/avatar-s-1.png';
        if ( sessionStorage.companyLogo != 'undefined' && sessionStorage.companyLogo != undefined )
            $scope.companyLogo = 'uploads/' + sessionStorage.companyLogo;

        $scope.today = ( new Date() ).toString();
    }

    $scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'ExpenseBySupplierSummaryCtrl', ['$scope', 'RprtService', 'CRUDService', 'MiscService', ExpenseBySupplierSummaryCtrl ] );