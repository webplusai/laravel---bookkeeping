'use strict';

function NewBillCtrl( $scope, $state, $stateParams, MiscService, TRXNService ) {

	$scope.preloadCounter = 0;

	$scope.retrieve = function() {
		TRXNService.retrieve( 'bill', $stateParams.trxnId ).done( function ( response ) {
            $scope.transaction = response.data.content;

            $scope.transaction.transaction.supplier = $scope.supplierUINames[ $scope.transaction.transaction.payee_id ];
            
            for ( var i = 0; i < $scope.transaction.billItems.length; i++ ) {
                $scope.transaction.billItems[i].removeIndex = i;
                $scope.transaction.billItems[i].product_service = $scope.productServiceUINames[ $scope.transaction.billItems[i].product_service_id ];
            }

            for ( var i = 0; i < $scope.transaction.billAccounts.length; i++ ) {
                $scope.transaction.billAccounts[i].removeIndex = i;
                $scope.transaction.billAccounts[i].account = $scope.accountUINames[ $scope.transaction.billAccounts[i].account_id ];
            }
            sessionStorage.preset = 0;

            CommonFunc().hidePreloader( '.full-screen-loader' );
        });
	}

	$scope.calculateTotal = function() {
		var total = 0;
		var accounts = $scope.transaction.billAccounts;
		var items = $scope.transaction.billItems;

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
        $scope.reArrangeRankNumbers( $scope.transaction.billItems );
        $scope.reArrangeRankNumbers( $scope.transaction.billAccounts );
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

            if ( $stateParams.trxnId > 0 ) {
	        	$scope.retrieve();
	        } else {
	        	CommonFunc().hidePreloader( '.full-screen-loader' );
	        }
		} );

		MiscService.retrieveAccountNamesByCategoryType().done( function( response ) {
            $scope.accountNamesByCategoryType = response;
        } );
	}

	$scope.initialize();

}

angular
	.module( 'bookkeeping' )
	.controller( 'NewBillCtrl', [ '$scope', '$state', '$stateParams', 'MiscService', 'TRXNService', NewBillCtrl ] );