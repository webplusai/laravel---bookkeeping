'use strict';

function SejNewExpensesDropdown() {

	var output = {};

	output.templateUrl = 'app/directives/SejNewExpensesDropdown/SejNewExpensesDropdown.html';

	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'sejNewExpensesDropdown', SejNewExpensesDropdown );