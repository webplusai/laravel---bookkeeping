'use strict';

function TRXNService( $q, $http ) {

	var output = {};
	var baseURL = 'saudisms/whm/api/trxn';

	output.create = function( endPoint, data ) {

		var deferred = $.Deferred();

		$http.post( baseURL + '/' + endPoint, data )
			.then
			(
				function ( response ) {
					deferred.resolve( response );
				},
				function ( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.retrieve = function( endPoint, trxnId ) {

		var deferred = $.Deferred();
		var URL = baseURL + '/' + endPoint + '/' + trxnId + '?endPointId=' + endPoint;

		$http.get( URL )
			.then
			(
				function ( response ) {
					deferred.resolve( response );
				},
				function ( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.update = function( endPoint, trxnId, data ) {

		var deferred = $.Deferred();

		$http.put( baseURL + '/' + endPoint + '/' + trxnId, data )
			.then
			(
				function ( response ) {
					deferred.resolve( response );
				},
				function ( response ) {
					deferred.reject( response );
				}
			);

		return deferred.promise();
	}

	output.delete = function( endPoint, trxnId ) {

		var deferred = $.Deferred();

		$http.delete( baseURL + '/' + endPoint + '/' + trxnId, { params: { endPointId: endPoint } } )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.resolve( response );
				}
			);

		return deferred.promise();
	}

	output.recoverDelete = function( endPoint, trxnId ) {

		var deferred = $.Deferred();

		$http.get( baseURL + '/' + 'recover_' + endPoint + '/' + trxnId + '?endPointId=' + endPoint )
			.then
			(
				function( response ) {
					deferred.resolve( response );
				},
				function( response ) {
					deferred.resolve( response );
				}
			);

		return deferred.promise();
	}

	return output;
}

angular
	.module( 'bookkeeping' )
	.factory( 'TRXNService', [ '$q', '$http', TRXNService ] );