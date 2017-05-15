'use strict';

function RprtService( $q, $http ) {

	var output = {};
	var baseURL = 'saudisms/whm/api/report';

	output.retrieveReport = function( endPoint, period, account ) {

		var deferred = $.Deferred();
		var url = baseURL + endPoint;

		if ( typeof period != 'undefined' )
			url += '/' + period;
		if ( typeof account != 'undefined' )
			url += '/' + account;

		$http.get( url )
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
	.factory( 'RprtService', [ '$q', '$http', RprtService ] );