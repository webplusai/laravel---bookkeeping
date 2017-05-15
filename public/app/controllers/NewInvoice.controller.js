'use strict';

function NewInvoiceCtrl( $scope, $state, $stateParams, MiscService, TRXNService ) {

	$scope.transaction 	=	{};

    $scope.customers                =   [];
	$scope.customerNames 			= 	[];
    $scope.customerUINames			=	[];
    $scope.productServices          =   [];
    $scope.productServiceNames 		= 	[];
    $scope.productServiceUINames	=	[];

    $scope.customerInfo             =   {};

	$scope.retrieve = function() {
		TRXNService.retrieve( 'invoice', $stateParams.trxnId ).done( function ( response ) {
            $scope.transaction = response.data.content;
            $scope.transaction.transaction.customer = $scope.customerUINames[ $scope.transaction.transaction.customer_id ];

        	for ( var i = 0; i < $scope.transaction.invoiceItems.length; i++ ) {
                $scope.transaction.invoiceItems[i].removeIndex = i;
                $scope.transaction.invoiceItems[i].product_service = $scope.productServiceUINames[ $scope.transaction.invoiceItems[i].product_service_id ];
            }
            $scope.calculateDiscount();
            sessionStorage.preset = 0;
        } );
	}

    $scope.calculateDiscount = function() {
        var discountTypeIDs = { 'Discount percent' : 1, 'Discount value' : 2, 'No discount' : 3 };

        if ( $scope.transaction.invoice.discount_type_id == discountTypeIDs[ 'Discount percent' ] ) {
            $scope.discountValue = $scope.transaction.invoice.sub_total * $scope.transaction.invoice.discount_amount / 100;
        } else if ( $scope.transaction.invoice.discount_type_id == discountTypeIDs[ 'Discount value' ] ) {
            $scope.discountValue = $scope.transaction.invoice.discount_amount;
        } else {
            $scope.discountValue = 0;
        }
        $scope.calculateTotal();
    }

    $scope.calculateTotal = function() {
        var total = $scope.transaction.invoice.sub_total;
        
        if ( !isNaN( $scope.discountValue ) )
            total -= parseFloat( $scope.discountValue );
        if ( !isNaN( $scope.transaction.invoice.shipping ) )
            total += parseFloat( $scope.transaction.invoice.shipping );

        $scope.transaction.transaction.total = total;
        $scope.transaction.transaction.balance = total;
        $scope.transaction.balanceDue = $scope.transaction.transaction.total - $scope.transaction.invoice.deposit;
    }

    $scope.sortableStop = function() {
        $scope.reArrangeRankNumbers( $scope.transaction.invoiceItems );
        $scope.calculateAmount( $scope.transaction.invoiceItems[0], $scope.transaction.invoiceItems );
    }

	$scope.initialize = function() {
		TrxnFunc( $scope, $state, $stateParams, MiscService, TRXNService ).initialize();

		MiscService.massRetrieve( [ 'customer', 'product_service', 'product_category', 'company_profile' ] ).done( function( response ) {
			var content = response.data.content;

            $scope.customers = content.customer;
			$scope.customerNames = MiscService.extractNameList( MiscService.extractActiveList( content.customer ) );
			$scope.customerUINames = MiscService.extractUniqueNameList( content.customer );
            $scope.productServices = content.productService;
			$scope.productServiceNames = MiscService.extractNameList( MiscService.extractActiveList( content.productService ) );
			$scope.productServiceUINames = MiscService.extractUniqueNameList( content.productService );
            $scope.productCategoryNames = MiscService.extractNameList( content.productCategory );

            if ( content.productCategory != undefined && content.productCategory.length )
                $scope.newProductService.product_category = content.productCategory[0].name;

            MiscService.getInvoiceNumber( $stateParams.trxnId ).done( function( response ) {
                $scope.transaction.transaction.invoice_receipt_no = response.data.content;
            } );

            if ( $stateParams.trxnId > 0 ) {
                $scope.retrieve();
            }

            $scope.companyProfile = content.companyProfile[0];
		} );

        $scope.$watch( function() {
            return $scope.transaction.transaction.customer;
        }, function( newVal, oldVal ) {
            for ( var i = 0; i < $scope.customers.length; i++ ) {
                if ( newVal == $scope.customers[i].name ) {
                    $scope.customerInfo = $scope.customers[i];
                }
            }
        } );

    }

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'NewInvoiceCtrl', [ '$scope', '$state', '$stateParams', 'MiscService', 'TRXNService', NewInvoiceCtrl ] )
