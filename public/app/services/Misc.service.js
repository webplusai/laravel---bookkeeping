'use strict';

function MiscService( $q, $http, CRUDService ) {

	var output = {};
	var baseURL = 'saudisms/whm/api/misc';

	output.activatePerson = function( id, data ) {

		var deferred = $.Deferred();

		data.endPointId = 'activate' + data.tableName[0].toUpperCase() + data.tableName.substr( 1 );
		$http.post( baseURL + '/activate_' + data.tableName + '/' + id, data )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.getInvoiceById = function( invoiceId ) {

		var deferred = $.Deferred();

		$http.get( baseURL + '/get_invoice_by_id?endPointId=getInvoiceById&invoiceId=' + invoiceId )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.getBillByExpenseId = function( expenseId ) {

		var deferred = $.Deferred();

		$http.get( baseURL + '/get_bill_by_expense_id?endPointId=getBillByExpenseId&expenseId=' + expenseId )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.getInvoiceNumber = function( salesId ) {

		var deferred = $.Deferred();

		$http.get( baseURL + '/get_invoice_number?endPointId=getInvoiceNumber&salesId=' + salesId )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.reject( response );
				}
			)

		return deferred.promise();
	}

	output.getUserName = function() {

		var deferred = $.Deferred();

		$http.get( baseURL + '/get_user_name?endPointId=getUserName' )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.setUserProfile = function( data ) {

		var deferred = $.Deferred();

		data.endPointId = 'setUserProfile';

		$http.put( baseURL + '/set_user_profile', data )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.massRetrieve = function( tableNames ) {

		var deferred = $.Deferred();

		$http.get( baseURL + '/mass_retrieve' + '?endPointId=massRetrieve', { params: { tableNames: JSON.stringify( tableNames ) } } )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.massUpdate = function( data ) {

		var deferred = $.Deferred();

		data.endPointId = "massUpdate";

		$http.put( baseURL + '/mass_update', data )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}
	
	output.retrieveAccountDetailTypeNames = function( accountCategoryType ) {

		var deferred = $.Deferred();

		$http.get( baseURL + '/retrieve_account_detail_type_names?endPointId=retrieveAccountDetailTypeNames&accountCategoryType=' + accountCategoryType )
			.then
			(
				function( response ) {
					deferred.resolve( response.data.content );
				},
				function( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.retrieveAccountNamesByCategoryType = function() {

		var deferred = $.Deferred();

		$http.get( baseURL + '/retrieve_account_names_by_category_type?endPointId=retrieveAccountNamesByCategoryType' )
			.then
			(
				function( response ) {
					deferred.resolve( response.data.content );
				},
				function( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.retrieveAuditHistory = function( trxnType, trxnId ) {

		var deferred = $.Deferred();

		$http.get( baseURL + '/retrieve_audit_history?endPointId=retrieveAuditHistory&trxnType=' + trxnType + '&trxnId=' + trxnId )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.retrieveCustomerInvoicesAndCreditNotes = function( customerName ) {

		var deferred = $.Deferred();

		$http.get( baseURL + '/retrieve_customer_invoices_and_credit_notes?endPointId=retrieveCustomerInvoicesAndCreditNotes&customerName=' + customerName )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( resposne ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.retrieveSupplierBillsAndSupplierCredits = function( supplierName ) {

		var deferred = $.Deferred();

		$http.get( baseURL + '/retrieve_supplier_bills_and_supplier_credits?endPointId=retrieveSupplierBillsAndSupplierCredits&supplierName=' + supplierName )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.extractNameList = function( list ) {
		var result = [];
		for ( var i = 0; i < list.length; i++) {
			result.push( list[i].name );
		}
		return result;
	}

	output.extractUniqueList = function( list ) {
		var result = [ {name: '', id: 0} ];
		var defaultIndex = 0;
		for ( var i = 0; i < list.length; i++) {
			if ( defaultIndex >= list[i].id - 1 )
				result.push( list[i] );
			else {
				result.push( { name: '', id: defaultIndex } );
				i --;
			}
			defaultIndex ++;
		}
		return result;
	}

	output.extractUniqueNameList = function( list ) {
		return output.extractNameList( output.extractUniqueList( list ) );
	}

	output.extractActiveList = function( list ) {
		var result = [];
		for( var i = 0; i < list.length; i++ ) {
			if ( list[i].is_active == 1 )
				result.push( list[i] );
		}
		return result;
	}

	return output;
}

angular
	.module( 'bookkeeping' )
	.factory( 'MiscService', [ '$q', '$http', 'CRUDService', MiscService ] );