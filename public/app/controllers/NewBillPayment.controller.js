'use strict';

function NewBillPayment( $scope, $state, $stateParams, MiscService, TRXNService ) {

	$scope.accountNames = [];
	$scope.accountUINames = [];
	$scope.customerNames = [];
	$scope.customerUINames = [];
    $scope.customerInvoices = [];

    $scope.checkBill = function( bill ) {
        if ( bill.checked == 1 )
            bill.amount = bill.balance;
        else
            bill.amount = 0;

        $scope.calculateBillPaymentTotal();
    }

    $scope.checkSupplierCredit = function( supplierCredit ) {
        if ( supplierCredit.checked == 1 ) {
            supplierCredit.amount = supplierCredit.balance <= $scope.transaction.transaction.total ? supplierCredit.balance : $scope.transaction.transaction.total;
        }
        else
            supplierCredit.amount = 0;

        $scope.calculateBillPaymentTotal();
    }

    $scope.checkAllBills = function() {

        var billCount = $scope.transaction.transaction.supplierBills.length;
        for ( var i = 0; i < billCount; i++ ) {
            var bill = $scope.transaction.transaction.supplierBills[i];
            bill.checked = $scope.transaction.allBillsChecked;

            if ( $scope.transaction.allBillsChecked == true )
                bill.amount = bill.balance;
            else
                bill.amount = 0;
        }
        
        $scope.calculateBillPaymentTotal();
    }

    $scope.checkAllSupplierCredits = function() {

        var supplierCreditCount = $scope.transaction.transaction.supplierCredits.length;
        var billPaymentTotal = $scope.transaction.transaction.total;

        for ( var i = 0; i < supplierCreditCount; i++ ) {
            var supplierCredit = $scope.transaction.transaction.supplierCredits[i];
            supplierCredit.checked = $scope.transaction.allSupplierCreditsChecked;

            if ( $scope.transaction.allSupplierCreditsChecked == true ) {
                supplierCredit.amount = supplierCredit.balance <= billPaymentTotal ? supplierCredit.balance : billPaymentTotal;
                billPaymentTotal -= supplierCredit.amount;
            }
            else
                supplierCredit.amount = 0;
        }

        $scope.calculateBillPaymentTotal();
    }

    $scope.setBillAmount = function( bill ) {
        if ( bill.amount > bill.balance )
            bill.amount = bill.balance;

        $scope.calculateBillPaymentTotal();
    }

    $scope.setSupplierCreditAmount = function( supplierCredit ) {
        if ( supplierCredit.amount > supplierCredit.balance )
            supplierCredit.amount = supplierCredit.balance;

        var billPaymentTotal = 0;
        for ( var i = 0; i < $scope.transaction.transaction.supplierBills.length; i++ ) {
            var supplierBill = $scope.transaction.transaction.supplierBills[i];
            if ( supplierBill.checked == true ) {
                billPaymentTotal += supplierBill.amount;
            }
        }

        for ( var i = 0; i < $scope.transaction.transaction.supplierCredits.length; i++ ) {
            var supplierCredit = $scope.transaction.transaction.supplierCredits[i];
            if ( supplierCredit.checked == true ) {
                billPaymentTotal -= supplierCredit.amount;
            }
        }

        if ( billPaymentTotal < 0 )
            supplierCredit.amount = 0;

        $scope.calculateBillPaymentTotal();
    }

    $scope.calculateBillPaymentTotal = function() {
        $scope.transaction.transaction.total = 0;

        for ( var i = 0; i < $scope.transaction.transaction.supplierBills.length; i++ ) {
            var supplierBill = $scope.transaction.transaction.supplierBills[i];
            if ( supplierBill.checked == true ) {
                $scope.transaction.transaction.total += supplierBill.amount;
            }
        }

        if ( $scope.transaction.transaction.supplierCredits ) {
            for ( var i = 0; i < $scope.transaction.transaction.supplierCredits.length; i++ ) {
                var supplierCredit = $scope.transaction.transaction.supplierCredits[i];
                if ( supplierCredit.checked == true ) {
                    $scope.transaction.transaction.total -= supplierCredit.amount;
                }
            }
        }

    }

    $scope.retrieve = function() {
        TRXNService.retrieve( 'bill_payment', $stateParams.trxnId ).done( function ( response ) {
            $scope.transaction = response.data.content;
            var supplierBills = $scope.transaction.transaction.supplierBills;
            var supplierCredits = $scope.transaction.transaction.supplierCredits;

            for ( var i = 0; i < supplierBills.length; i++ ) {
                supplierBills[i].amount = supplierBills[i].payment;
                if ( !isNaN( supplierBills[i].amount ) )
                    supplierBills[i].balance += supplierBills[i].amount;
                if ( supplierBills[i].checked == '1' )
                    supplierBills[i].checked = true;
            }

            for ( var i = 0; i < supplierCredits.length; i++ ) {
                supplierCredits[i].amount = supplierCredits[i].payment;
                if ( !isNaN( supplierCredits[i].amount ) )
                    supplierCredits[i].balance += supplierCredits[i].amount;
                if ( supplierCredits[i].checked == '1' )
                    supplierCredits[i].checked = true;
            }
        } );
    }

    $scope.retrieveSupplierBillsAndSupplierCredits = function() {
        MiscService.retrieveSupplierBillsAndSupplierCredits( $scope.transaction.transaction.supplier ).done( function( response ) {
            $scope.transaction.transaction.supplierBills = response.data.content.supplierBills;
            $scope.transaction.transaction.supplierCredits = response.data.content.supplierCredits;
        } );
    }

    $scope.searchByExpenseId = function() {
        MiscService.getBillByExpenseId( $stateParams.expenseId ).done( function( response ) {
            var data = response.data.content;
            $scope.transaction.transaction.supplier = $scope.supplierUINames[ data.payee_id ];
            $scope.transaction.transaction.supplierBills = data.supplierBills;
            $scope.transaction.transaction.supplierCredits = data.supplierCredits;
            $scope.transaction.transaction.total = data.total;
        } );
    }

	$scope.initialize = function() {
		TrxnFunc( $scope, $state, $stateParams, MiscService, TRXNService ).initialize();
        $scope.transaction.payment.account = 'Cash';

        if ( $scope.transaction.transaction.customer ) {
            $scope.retrieveSupplierBillsAndSupplierCredits();
        }

		MiscService.massRetrieve( [ 'account', 'account_category_type', 'account_detail_type', 'supplier' ] ).done( function( response ) {
			var content = response.data.content;

			$scope.accountNames = MiscService.extractNameList( content.account );
			$scope.accountUINames = MiscService.extractUniqueNameList( content.account );
			$scope.supplierNames = MiscService.extractNameList( MiscService.extractActiveList( content.supplier ) );
			$scope.supplierUINames = MiscService.extractUniqueNameList( content.supplier );

		    $scope.accountCategoryTypeNames = MiscService.extractNameList( content.accountCategoryType );
            $scope.accountCategoryTypeUINames = MiscService.extractUniqueNameList( content.accountCategoryType );
            $scope.accountDetailTypeUINames = MiscService.extractUniqueNameList( content.accountDetailType );
            $scope.account.account_category_type = content.accountCategoryType[0].name;
            $scope.getAccountDetailTypeNames();

            if ( $stateParams.expenseId ) {
                $scope.searchByExpenseId();
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
	.controller( 'NewBillPayment', [ '$scope', '$state', '$stateParams', 'MiscService', 'TRXNService', NewBillPayment ] );