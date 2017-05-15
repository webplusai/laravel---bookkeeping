'use strict';

function SejNewSalesDropdown() {

	var output = {};

	output.templateUrl = 'app/directives/SejNewSalesDropdown/SejNewSalesDropdown.html';

	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'sejNewSalesDropdown', SejNewSalesDropdown );