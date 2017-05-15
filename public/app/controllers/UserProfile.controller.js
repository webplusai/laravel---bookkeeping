'use strict';

function UserProfileCtrl( $scope, $window, $timeout, CRUDService, MiscService ) {

	$scope.user = {};

	$scope.getUserProfile = function() {
		CRUDService.retrieve( 'user_profile' ).done( function( response ) {
			if ( response.data.content.length != 0 ) {
				$scope.user = response.data.content[0];
				$scope.user.tableName = 'user_profile';
				$scope.createOrUpdate = 'update';

				$timeout(function(){
				    $( "#countryDropdown" ).val( $scope.user.country ).trigger( 'change' );
				});
			}
		} );
	}

	$scope.setUserProfile = function() {
		MiscService.setUserProfile( $scope.user ).done( function( response ) {
			$scope.errorMessages = [];
			toastr.success( 'Successfully Saved' );
			sessionStorage.userName = $scope.user.name;
		} ).fail( function( response ) {
			$scope.errorMessages = response.data.content;
		} );
	}

	$scope.goBack = function() {
		$window.history.back();
	}

	$scope.initialize = function() {

		CommonFunc().initializeCountryDropdown( '#countryDropdown' );

		$scope.getUserProfile();
		CommonFunc().initializeValidation( 'form.form', function( $form, errors ) {
			$scope.setUserProfile();
		} );
	}

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'UserProfileCtrl', [ '$scope', '$window', '$timeout', 'CRUDService', 'MiscService', UserProfileCtrl ] );