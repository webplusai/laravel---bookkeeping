'use strict';

function NewSupplierCreditCtrl( $scope, $state, $stateParams, MiscService, TRXNService ) {

	$scope.retrieve = function() {
		TRXNService.retrieve( 'supplier_credit', $stateParams.trxnId ).done( function ( response ) {
            $scope.transaction = response.data.content;

            for ( var i = 0; i < $scope.transaction.supplierCreditItems.length; i++ ) {
                $scope.transaction.supplierCreditItems[i].removeIndex = i;
            }

            for ( var i = 0; i < $scope.transaction.supplierCreditAccounts.length; i++ ) {
                $scope.transaction.supplierCreditAccounts[i].removeIndex = i;
            }
            sessionStorage.preset = 0;
        });
	}

	$scope.setPayeeType = function( list, type ) {
		for ( var i = 0; i < list.length; i++ ) 
			list[i].type = type;
		return list;
	}

	$scope.calculateTotal = function() {
		var total = 0;
		var accounts = $scope.transaction.supplierCreditAccounts;
		var items = $scope.transaction.supplierCreditItems;

		for ( var i = 0; i < accounts.length; i++ ) {
			if ( accounts[i].rank != undefined && !isNaN( accounts[i].amount ) ) {
				total += parseFloat( accounts[i].amount );
			}
		}

		for ( var i = 0; i < items.length; i++ ) {
			if ( items[i].rank != undefined && !isNaN( items[i].amount ) ) {
				total += parseFloat( items[i].amount );
			}
		}

		$scope.transaction.transaction.total = total;
		$scope.transaction.transaction.balance = total;
	}

	$scope.sortableStop = function() {
        $scope.reArrangeRankNumbers( $scope.transaction.supplierCreditItems );
        $scope.reArrangeRankNumbers( $scope.transaction.supplierCreditAccounts );
    }

	$scope.initialize = function() {
		TrxnFunc( $scope, $state, $stateParams, MiscService, TRXNService ).initialize();

		MiscService.massRetrieve( [ 'account', 'account_category_type', 'account_detail_type', 'product_category', 'product_service', 'supplier' ] ).done( function( response ) {
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

            $scope.productServices = content.productService;
            $scope.productServiceNames = MiscService.extractNameList( MiscService.extractActiveList( content.productService ) );
            $scope.productServiceUINames = MiscService.extractUniqueNameList( content.productService );
            $scope.productCategoryNames = MiscService.extractNameList( content.productCategory );
            if ( content.productCategory[0] )
            	$scope.newProductService.product_category = content.productCategory[0].name;
		} );

		MiscService.retrieveAccountNamesByCategoryType().done( function( response ) {
            $scope.accountNamesByCategoryType = response;
        } );

        if ( $stateParams.trxnId > 0 ) {
        	$scope.retrieve();
        }
	}

	$scope.initialize();

}

angular
	.module( 'bookkeeping' )
	.controller( 'NewSupplierCreditCtrl', [ '$scope', '$state', '$stateParams', 'MiscService', 'TRXNService', NewSupplierCreditCtrl ] );