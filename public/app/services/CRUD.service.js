'use strict';

function CRUDService( $q, $http ) {

	var output = {};
	var baseURL = 'saudisms/whm/api/crud';

	output.create = function( data ) {

		var deferred = $.Deferred();

		$http.post( baseURL, data )
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

	output.retrieve = function( table_name, id ) {

		var deferred = $.Deferred();
		var endPoint = baseURL;
		
		if ( id ) {
			endPoint += '/' + id;
		}

		$http.get( endPoint + '?tableName=' + table_name )
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

	output.update = function( id, data ) {

		var deferred = $.Deferred();

		$http.put( baseURL + '/' + id, data )
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

	output.delete = function( id, data ) {

		var deferred = $.Deferred();
		
		$http.delete( baseURL + '/' + id, { params: data } )
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

	return output;
}

angular
	.module( 'bookkeeping' )
	.factory( 'CRUDService', [ '$q', '$http', CRUDService ] );