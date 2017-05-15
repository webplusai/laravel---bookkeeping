'use strict';

function SejScreenPreLoader() {

	var output = {};

	output.transclude = true;
	output.templateUrl = 'app/directives/SejScreenPreloader/SejScreenPreLoader.html';

	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'sejScreenPreLoader', SejScreenPreLoader );