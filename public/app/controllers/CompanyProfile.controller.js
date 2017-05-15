'use strict';

function CompanyProfileCtrl( $scope, $window, $timeout, CRUDService ) {

	$scope.company = {};
	$scope.createOrUpdate = 'create';

	$scope.getCompanyProfile = function() {
		CRUDService.retrieve( 'company_profile' ).done( function( response ) {
			if ( response.data.content.length != 0 ) {
				$scope.company = response.data.content[ 0 ];
				$scope.company.tableName = 'company_profile';
				$scope.createOrUpdate = 'update';

				$timeout(function(){
				    $( "#countryDropdown" ).val( $scope.company.country ).trigger( 'change' );
				});
			}

			CommonFunc().hidePreloader( '.full-screen-loader' );
		} );
	}

	$scope.setCompanyProfile = function() {
		if ( $scope.createOrUpdate == 'create' ) {
			CRUDService.create( $scope.company ).done( function( response ) {
				$scope.errorMessages = [];
				$scope.createOrUpdate = 'update';
				toastr.success( 'Successfully Saved' );
				sessionStorage.companyName = $scope.company.company_name;
				sessionStorage.companyLogo = $scope.company.company_logo;
			} ).fail( function( response ) {
				$scope.errorMessages = response.data.content;
			} );
		} else {
			CRUDService.update( 1, $scope.company ).done( function( response ) {
				$scope.errorMessages = [];
				toastr.success( 'Successfully Updated' );
				sessionStorage.companyName = $scope.company.company_name;
				sessionStorage.companyLogo = $scope.company.company_logo;
			} ).fail( function( response ) {
				$scope.errorMessages = response.data.content;
			} );
		}
	}

	$scope.goBack = function() {
		$window.history.back();
	}

	$scope.initialize = function() {

		CommonFunc().initializeCountryDropdown( '#countryDropdown' );

		$scope.getCompanyProfile();
		var dropzone = new Dropzone( '#dpz-single-file', {
			url: "saudisms/whm/api/upload",
			maxFiles: 1,
			uploadMultiple: false,
			autoProcessQueue: false,
			acceptedFiles: 'image/*',
			headers: {
				'Authorization': 'Bearer ' + sessionStorage.access_token
			},
			init: function() {
				this.on( 'maxfilesexceeded', function( file ) {
					this.removeAllFiles();
					this.addFile( file );
				} );
				this.on( 'success', function( file, response ) {
					$scope.company.company_logo = response;
					$scope.setCompanyProfile();
				} );
			}
		} );
		CommonFunc().initializeValidation( 'form.form', function( $form, errors ) {
			if ( dropzone.files.length > 0 )
				dropzone.processQueue();
			else
				$scope.setCompanyProfile();
		} );
	}

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'CompanyProfileCtrl', [ '$scope', '$window', '$timeout', 'CRUDService', CompanyProfileCtrl ] );