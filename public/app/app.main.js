'use strict';

function AppCtrl( $scope, $http, $localStorage ) {
	
}

angular
    .module( 'bookkeeping' )
    .controller( 'AppCtrl', [ '$scope', '$http', '$localStorage', AppCtrl ] );