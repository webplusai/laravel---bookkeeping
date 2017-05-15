'use strict';

function SejPersonTypeDialog( CRUDService, MiscService ) {

	var output = {}

	output.transclude = true;
	output.templateUrl = 'app/directives/SejPersonTypeDialog/SejPersonTypeDialog.html';
	output.link = function( scope, element, attrs ) {
		
		$( document ).mouseup( function( e ) {
			var container = $( '.slt-open-dd' );

	      	if ( !container.is( e.target ) && container.has( e.target ).length === 0 ) {
	        	container.hide();
	      	}
		} );

		scope.setPersonType = function() {
			scope.newPerson.tableName = scope.targetTableName;
			scope.person.tableName = scope.targetTableName;
		}

		scope.initialize = function() {
			CommonFunc().initializeValidation( 'form.form-person-type', function( $form, errors ) {
				CRUDService.create( scope.person ).done( function( response ) {
					MiscService.massRetrieve( [ 'customer', 'supplier' ] ).done( function( response ) {
	        			var content = response.data.content;

	        			if ( scope.personListType == 'customer')
	        				scope.customerNames = MiscService.extractNameList( content.customer );
	        			else if ( scope.personListType == 'supplier' )
	        				scope.supplierNames = MiscService.extractNameList( content.supplier );
	        			else if ( scope.personListType == 'payee' )
	        				scope.customerNames = scope.setPayeeType( content.customer, 'Customer' ).concat( scope.setPayeeType( content.supplier, 'Supplier' ) );

	        			$( '.select-person-type' ).hide();
	        		} );
				} ).fail( function( response ) {
					scope.personTypeDialogErrorMessages = response.data.content;
				} );
			} );
		}

		scope.initialize();
	}

	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'sejPersonTypeDialog', [ 'CRUDService', 'MiscService', SejPersonTypeDialog ] );