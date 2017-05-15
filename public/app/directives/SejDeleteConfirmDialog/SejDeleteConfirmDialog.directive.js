'use strict';

function SejDeleteConfirmDialog( $state, $stateParams, TRXNService ) {

	var output = {};

	output.transclude = true;
	output.templateUrl = 'app/directives/SejDeleteConfirmDialog/SejDeleteConfirmDialog.html';
	output.link = function( scope, element, attrs ) {

		scope.targetName = attrs.targetName;

		scope.showDeleteTransactionDialog = function() {
		
			if ( $stateParams.trxnId > 0 ) {
				$( "#" + attrs.id ).modal( 'show' );
			}
		}

		scope.deleteTransaction = function() {
	        if ( $stateParams.trxnId > 0 ) {
	            TRXNService.delete( scope.transactionType, $stateParams.trxnId ).done( function( response ) {
	                scope.errorMessages = [];
	                toastr.success( 'Successfully deleted' );
	                $( "#" + attrs.id ).modal( 'hide' );
	                setTimeout( function() {
	                	if ( scope.transactionType == 'invoice' || scope.transactionType == 'payment' || scope.transactionType == 'sales_receipt' || scope.transactionType == 'credit_note' ) {
	                		$state.go( 'app.main.sales.main' );
	                	} else if ( scope.transactionType == 'expense' || scope.transactionType == 'bill' || scope.transactionType == 'bill_payment' || scope.transactionType == 'supplier_credit' ) {
	                		$state.go( 'app.main.expense.main' );
	                	} else if ( scope.transactionType == 'journal_entry' ) {
	                		$state.go( 'app.main.other.journal-entry' );
	                	}
	                }, 1000 );
	            } );
	        }
	    }

		function initialize() {
			CommonFunc().initializeValidation( 'form.form-horizontal.form-delete-confirm', function( $form, errors ) {
				scope.$eval( attrs.deleteEvent ) ();
			} );
		}

		initialize();
	}

	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'sejDeleteConfirmDialog', [ '$state', '$stateParams', 'TRXNService', SejDeleteConfirmDialog ] );