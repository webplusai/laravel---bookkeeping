'use strict';

function NewPaymentCtrl( $scope, $state, $stateParams, MiscService, TRXNService ) {

	$scope.accountNames = [];
	$scope.accountUINames = [];
	$scope.customerNames = [];
	$scope.customerUINames = [];
    $scope.customerInvoices = [];

    $scope.checkInvoice = function( invoice ) {
        if ( invoice.checked == 1 )
            invoice.amount = invoice.balance;
        else
            invoice.amount = 0;

        $scope.calculateAmountReceived();
    }

    $scope.checkCreditNote = function( creditNote ) {
        if ( creditNote.checked == 1 )
            creditNote.amount =  creditNote.balance <= $scope.transaction.transaction.total ? creditNote.balance : $scope.transaction.transaction.total;
        else
            creditNote.amount = 0;

        $scope.calculateAmountReceived();
    }

    $scope.checkAllInvoices = function() {

        var invoiceCount = $scope.transaction.transaction.customerInvoices.length;
        for ( var i = 0; i < invoiceCount; i++ ) {
            var invoice = $scope.transaction.transaction.customerInvoices[i];
            invoice.checked = $scope.transaction.allInvoicesChecked;

            if ( $scope.transaction.allInvoicesChecked == true )
                invoice.amount = invoice.balance;
            else
                invoice.amount = 0;
        }
        
        $scope.calculateAmountReceived();
    }

    $scope.checkAllCreditNotes = function() {

        var creditNoteCount = $scope.transaction.transaction.creditNotes.length;
        var paymentTotal = $scope.transaction.transaction.total;
        for ( var i = 0; i < creditNoteCount; i++) {
            var creditNote = $scope.transaction.transaction.creditNotes[i];
            creditNote.checked = $scope.transaction.allCreditNotesChecked;

            if ( $scope.transaction.allCreditNotesChecked == true ) {
                creditNote.amount =  creditNote.balance <= paymentTotal ? creditNote.balance : paymentTotal;
                paymentTotal -= creditNote.amount;
            }
            else
                creditNote.amount = 0;
        }

        $scope.calculateAmountReceived();
    }

    $scope.setInvoiceAmount = function( invoice ) {
        if ( invoice.amount > invoice.balance )
            invoice.amount = invoice.balance;

        $scope.calculateAmountReceived();
    }

    $scope.setCreditNoteAmount = function( creditNote ) {
        if ( creditNote.amount > creditNote.balance )
            creditNote.amount = creditNote.balance;

        var paymentTotal = 0;
        for ( var i = 0; i < $scope.transaction.transaction.customerInvoices.length; i++ ) {
            var customerInvoice = $scope.transaction.transaction.customerInvoices[i];
            if ( customerInvoice.checked == true ) {
                paymentTotal += customerInvoice.amount;
            }
        }

        for ( var i = 0; i < $scope.transaction.transaction.creditNotes.length; i++ ) {
            var note = $scope.transaction.transaction.creditNotes[i];
            if ( note.checked == true ) {
                paymentTotal -= note.amount;
            }
        }

        if ( paymentTotal < 0 ) {
            creditNote.amount = 0;
        }

        $scope.calculateAmountReceived();
    }

    $scope.calculateAmountReceived = function() {
        $scope.transaction.transaction.total = 0;

        for ( var i = 0; i < $scope.transaction.transaction.customerInvoices.length; i++ ) {
            var customerInvoice = $scope.transaction.transaction.customerInvoices[i];
            if ( customerInvoice.checked == true ) {
                $scope.transaction.transaction.total += customerInvoice.amount;
            }
        }

        if ( $scope.transaction.transaction.creditNotes ) {
            for ( var i = 0; i < $scope.transaction.transaction.creditNotes.length; i++ ) {
                var creditNote = $scope.transaction.transaction.creditNotes[i];
                if ( creditNote.checked == true ) {
                    $scope.transaction.transaction.total -= creditNote.amount;
                }
            }
        }
    }

    $scope.retrieve = function() {
        TRXNService.retrieve( 'payment', $stateParams.trxnId ).done( function ( response ) {
            $scope.transaction = response.data.content;
            var customerInvoices = $scope.transaction.transaction.customerInvoices;
            for ( var i = 0; i < customerInvoices.length; i++ ) {
                customerInvoices[i].amount = customerInvoices[i].payment;
                if ( !isNaN( customerInvoices[i].amount ) )
                    customerInvoices[i].balance += customerInvoices[i].amount;
                customerInvoices[i].checked = customerInvoices[i].checked == '1';
            }


            var creditNotes = $scope.transaction.transaction.creditNotes;
            for ( var i = 0; i < creditNotes.length; i++ ) {
                creditNotes[i].amount = creditNotes[i].payment;
                if ( !isNaN( creditNotes[i].amount ) )
                    creditNotes[i].balance += creditNotes[i].amount;
                creditNotes[i].checked = creditNotes[i].checked == '1';
            }
        } );
    }

    $scope.retrieveCustomerInvoicesAndCreditNotes = function() {
        MiscService.retrieveCustomerInvoicesAndCreditNotes( $scope.transaction.transaction.customer ).done( function( response ) {
            $scope.transaction.transaction.customerInvoices = response.data.content.customerInvoices;
            $scope.transaction.transaction.creditNotes = response.data.content.creditNotes;
        } );
    }

	$scope.searchByInvoiceId = function() {
        MiscService.getInvoiceById( $scope.transaction.payment.invoice_id ).done( function( response ) {
            var data = response.data.content;
            $scope.transaction.transaction.customer = $scope.customerUINames[ data.customer_id ];
            $scope.transaction.transaction.total = data.total;
            $scope.transaction.transaction.customerInvoices = data.customerInvoices;
            $scope.transaction.transaction.creditNotes = data.creditNotes;
            $scope.errorMessages = [];
        } ).fail( function( response ) {
            $scope.errorMessages = response.data.content;
        } );
    }

	$scope.initialize = function() {
		TrxnFunc( $scope, $state, $stateParams, MiscService, TRXNService ).initialize();
        $scope.transaction.payment.account = 'Cash';

        if ( $scope.transaction.transaction.customer ) {
            $scope.retrieveCustomerInvoicesAndCreditNotes();
        }

		MiscService.massRetrieve( [ 'account', 'account_category_type', 'account_detail_type', 'customer' ] ).done( function( response ) {
			var content = response.data.content;

			$scope.accountUINames = MiscService.extractUniqueNameList( content.account );
			$scope.customerNames = MiscService.extractNameList( MiscService.extractActiveList( content.customer ) );
			$scope.customerUINames = MiscService.extractUniqueNameList( content.customer );

		    $scope.accountCategoryTypeNames = MiscService.extractNameList( content.accountCategoryType );
            $scope.accountCategoryTypeUINames = MiscService.extractUniqueNameList( content.accountCategoryType );
            $scope.accountDetailTypeUINames = MiscService.extractUniqueNameList( content.accountDetailType );
            $scope.account.account_category_type = content.accountCategoryType[0].name;
            $scope.getAccountDetailTypeNames();

            if ( $stateParams.invoiceId ) {
                $scope.transaction.payment.invoice_id = $stateParams.invoiceId;
                $scope.searchByInvoiceId();
            }
		} );

        MiscService.retrieveAccountNamesByCategoryType().done( function( response ) {
            $scope.accountNamesByCategoryType = response;
        } );

        if ( $stateParams.trxnId > 0) {
        	$scope.retrieve();
        }
	}

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'NewPaymentCtrl', [ '$scope', '$state', '$stateParams', 'MiscService', 'TRXNService', NewPaymentCtrl ] );