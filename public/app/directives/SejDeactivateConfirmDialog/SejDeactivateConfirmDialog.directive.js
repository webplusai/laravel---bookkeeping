'use strict';

function SejDeactivateConfirmDialog( MiscService ) {

	var output = {};

	output.transclude = true;
	output.templateUrl = 'app/directives/SejDeactivateConfirmDialog/SejDeactivateConfirmDialog.html';
	output.link = function( scope, elem, attrs ) {
		scope.deactivatePerson = function() {
			MiscService.activatePerson( scope.personId, { is_active: 0, tableName: scope.targetTableName } ).done( function( response ) {
		    	toastr.success( "Successfully updated" );
		    	CommonFunc().redrawDataTable( scope.dataTable, response.data.content, scope.generateRow, scope.targetTableName );
		    	$( '#deactivateConfirmDialog' ).modal( 'hide' );
			} );
		}
	}

	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'sejDeactivateConfirmDialog', [ 'MiscService', SejDeactivateConfirmDialog ] );