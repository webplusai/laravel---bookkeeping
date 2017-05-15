'use strict';

function HeaderCtrl( $scope, $state, CRUDService, MiscService ) {

	$scope.getUserName = function() {
		MiscService.getUserName().done( function( response ) {
			sessionStorage.userName = response.data.content;
		} );
	}

	$scope.getCompanyName = function() {
		
		CRUDService.retrieve( 'company_profile', 1 ).done( function( response ) {
			if ( response.data.content != null )
				sessionStorage.companyName = response.data.content.company_name;
		} );
	}

	$scope.signout = function() {
		sessionStorage.access_token = null;
		sessionStorage.clear();
		$state.go( 'app.user.signin' );
	}

	$scope.initialize = function() {

		if ( sessionStorage.userName != '' ) {
			$scope.userName = sessionStorage.userName;
		} else {
			$scope.getUserName();
		}

		if ( sessionStorage.companyName != '' ) {
			$scope.companyName = sessionStorage.companyName;
		} else {
			$scope.getCompanyName();
		}

	}

	$scope.$watch( function() {
		return sessionStorage.companyName;
	}, function( newVal, oldVal ) {
		$scope.companyName = newVal;
	} );

	$scope.$watch( function() {
		return sessionStorage.userName;
	}, function( newVal, oldVal ) {
		$scope.userName = newVal;
	} );

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'HeaderCtrl', [ '$scope', '$state', 'CRUDService', 'MiscService', HeaderCtrl ] );