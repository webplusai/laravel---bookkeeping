'use strict';

function SejTrxnZeroWarningDialog( ) {

	var output = {};

	output.templateUrl = 'app/directives/SejTrxnZeroWarningDialog/SejTrxnZeroWarningDialog.html';

	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'sejTrxnZeroWarningDialog', SejTrxnZeroWarningDialog );