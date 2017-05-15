'use strict';

function SejPersonDialog( $timeout, CRUDService, MiscService ) {

	var output = {}

	output.transclude = true;
	output.templateUrl = 'app/directives/SejPersonDialog/SejPersonDialog.html';
	output.link = function( scope, element, attrs ) {

		scope.newPerson = { is_active: 1, balance: 0 };
		scope.person = {};

		scope.createPerson = function() {
	        CRUDService.create( scope.person ).done( function( response ) {
	        	if ( scope.dataTable ) {
	        		CommonFunc().appendRowToDataTable( scope.dataTable, scope.generateRow( response.data.content, 0, scope.targetTableName ) );
	        	} else {
	        		MiscService.massRetrieve( [ 'customer', 'supplier' ] ).done( function( response ) {
	        			var content = response.data.content;

	        			if ( scope.personListType == 'customer')
	        				scope.customerNames = MiscService.extractNameList( content.customer );
	        			else if ( scope.personListType == 'supplier' )
	        				scope.supplierNames = MiscService.extractNameList( content.supplier );
	        			else if ( scope.personListType == 'payee' )
	        				scope.customerNames = scope.setPayeeType( content.customer, 'Customer' ).concat( scope.setPayeeType( content.supplier, 'Supplier' ) );

	        		} );
	        	}

	        	$( '#' + attrs.id ).modal( 'hide' );
	        	toastr.success( 'Successfully created' );
	        	scope.person = CommonFunc().cloneObject( scope.newPerson );
	        } ).fail( function( response ) {
            	scope.personDialogErrorMessages = response.data.content;
            } );
		}

		scope.updatePerson = function( id, data ) {
			
			if ( ( data.tableName == 'customer' || data.tableName == 'supplier' ) && data.name == undefined && scope.personUIList[ id ].balance != 0 && scope.personUIList[ id ].is_active == 1 ) {
				$( '#deactivateConfirmDialog' ).modal( 'show' );
				scope.personId = id;
			} else {
				CRUDService.update( id, data ).done( function( response ) {
		        	$( '#' + attrs.id ).modal( 'hide' );
		        	toastr.success( "Successfully updated" );
		        	if ( scope.dataTable )
		            	CommonFunc().redrawDataTable( scope.dataTable, response.data.content, scope.generateRow, scope.targetTableName );
		        });
			}
		}

		scope.showCreatePersonDialog = function() {
	    	scope.isEditDialog = 0;
	        scope.personDialogErrorMessages = [];
	    	scope.person = CommonFunc().cloneObject( scope.newPerson );
	    	$( '#' + attrs.id ).modal( 'show' );
	    }

	    scope.showEditPersonDialog = function( personId ) {
	    	CRUDService.retrieve( scope.targetTableName, personId ).then( function( response ) {
	    		scope.isEditDialog = 1;
	            scope.personDialogErrorMessages = [];
	    		scope.person = response.data.content;
	    		scope.person.tableName = scope.targetTableName;
	    		$( '#' + attrs.id ).modal( 'show' );

	    		if ( scope.person.country != null ) {
		    		$timeout( function() {
					    $( "#countryDropdown" ).val( scope.person.country ).trigger( 'change' );
					} );
				}
	    	} );
	    }

		function initialize() {
			scope.newPerson.tableName = scope.targetTableName;
			scope.person = CommonFunc().cloneObject( scope.newPerson );
			CommonFunc().initializeValidation( "form.form-horizontal.form-person", function( $form, errors ) {
	        	if ( scope.isEditDialog ) {
	        		scope.updatePerson( scope.person.id, scope.person );
	        	} else {
	        		scope.$eval( attrs.createEvent )();
	        	}
	        } );

	        CommonFunc().initializeCountryDropdown( '#countryDropdown' );
		}
		
		initialize();
	}

	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'sejPersonDialog', [ '$timeout', 'CRUDService', 'MiscService', SejPersonDialog ] );