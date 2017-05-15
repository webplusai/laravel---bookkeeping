'use strict';

function AuthCtrl( $scope, $state, AuthService, CRUDService, MiscService ) {

	$scope.errorMessage = [];

	$scope.signin = function() {
		AuthService.signin(
			{
				email: $scope.email,
				password: $scope.password
			},
			function( response ) {
				MiscService.massRetrieve( [ 'user_profile', 'company_profile' ] ).done( function( response ) {
					var content = response.data.content;

					if ( content.companyProfile[0] ) {
						sessionStorage.companyName = content.companyProfile[0].company_name;
						sessionStorage.companyLogo = content.companyProfile[0].company_logo;
					}
					sessionStorage.userName = content.userProfile[0].name;
					$state.go( 'app.main.dashboard' );
				} );
				sessionStorage.access_token = response.data.content.access_token;
				toastr.success( 'Successfully signed in' );
			},
			function( response ) {
				$scope.errorMessages = response.data.content;
			}
		);
	}

	$scope.signup = function() {
		AuthService.signup(
			{
				name: $scope.name,
				email: $scope.email,
				password: $scope.password
			},
			function( response ) {
				toastr.success( 'Successfully signed up' );
				$state.go( 'app.user.signin' );
			},
			function( response ) {
				alert( JSON.stringify( response.data ) );
			}
		);
	}

	$scope.initialize = function() {
		CommonFunc().initializeValidation( 'form.form-horizontal' );
		CommonFunc().initializeCheckBox( '.chk-remember' );
		sessionStorage.clear();
	}

	$( '.full-screen-loader ').hide();

	$( document ).ready( function() {
		$( '.full-screen-loader ').hide();
	} );

	$scope.$on( '$viewContentLoaded', function(){
	    $( '.full-screen-loader ').hide();
	} );

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'AuthCtrl', [ '$scope', '$state', 'AuthService', 'CRUDService', 'MiscService', AuthCtrl ] );