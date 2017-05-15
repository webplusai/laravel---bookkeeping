'use strict';

function NewJournalEntryCtrl( $scope, $state, $stateParams, MiscService, TRXNService ) {

	$scope.setPayeeType = function( list, type ) {
		for ( var i = 0; i < list.length; i++ ) 
			list[i].type = type;
		return list;
	}

	$scope.retrieve = function() {
		TRXNService.retrieve( 'journal_entry', $stateParams.trxnId ).done( function ( response ) {
            $scope.transaction = response.data.content;

            $scope.transaction.transaction.total = 1;
        	for ( var i = 0; i < $scope.transaction.journalEntryItems.length; i++ ) {
                $scope.transaction.journalEntryItems[i].removeIndex = i;
                $scope.transaction.journalEntryItems[i].account = $scope.accountUINames[ $scope.transaction.journalEntryItems[i].account_id ];
            }

        } );
	}
	
	$scope.initialize = function() {
		TrxnFunc( $scope, $state, $stateParams, MiscService, TRXNService ).initialize();

		MiscService.massRetrieve( [ 'account', 'customer', 'supplier' ] ).done( function( response ) {
			var content = response.data.content;

			$scope.accountNames = MiscService.extractNameList( content.account );
			$scope.accountUINames = MiscService.extractUniqueNameList( content.account );

			$scope.customerNames = $scope.setPayeeType( content.customer , 'Customer' );
			$scope.customerUINames = MiscService.extractUniqueNameList( content.customer );
			$scope.customerNames = $scope.customerNames.concat( $scope.setPayeeType( content.supplier, 'Supplier' ) );

			if ( $stateParams.trxnId > 0 ) {
	            $scope.retrieve();
	        }
		} );

		MiscService.retrieveAccountNamesByCategoryType().done( function( response ) {
            $scope.accountNamesByCategoryType = response;
        } );
	}

	$scope.createOrUpdateJournalEntry = function( bAddNew ) {
		var debitTotal = 0;
		var creditTotal = 0;

		for( var i = 0; i < $scope.transaction.journalEntryItems.length; i++ ) {
			if ( $scope.transaction.journalEntryItems[i].account != '' ) {
				debitTotal += $scope.transaction.journalEntryItems[i].debits;
				creditTotal += $scope.transaction.journalEntryItems[i].credits;
			}
		}

		if ( debitTotal == creditTotal ) {
			if ( debitTotal == 0 )
				$( '#zeroWarningDialog' ).modal( 'show' );
			else
				$scope.createOrUpdate( bAddNew );
		} else {
			$scope.errorMessages = [ 'Please balance debits and credits' ];
		}
	}

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'NewJournalEntryCtrl', [ '$scope', '$state', '$stateParams', 'MiscService', 'TRXNService', NewJournalEntryCtrl ] );