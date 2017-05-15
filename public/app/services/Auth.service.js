'use strict';

function AuthService( $q, $http ) {

	var output = {};
	var baseURL = 'saudisms/whm/api';

	output.signin = function( data, onSuccess, onFailure ) {
		$http.post( baseURL + '/signin', data )
			.then
			(
				function( response ) {
					onSuccess( response );
				},
				function( response ) {
					onFailure( response );
				}
			);
	}

	output.signup = function( data, onSuccess, onFailure ) {
		$http.post( baseURL + '/signup', data )
			.then
			(
				function( response ) {
					onSuccess( response );
				},
				function( response ) {
					onFailure( response );
				}
			);
	}

	return output;
}

angular
	.module( 'bookkeeping' )
	.factory( 'AuthService', [ '$q', '$http', AuthService ] );