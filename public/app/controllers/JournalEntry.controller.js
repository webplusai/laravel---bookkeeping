'use strict';

function JournalEntryCtrl( $scope, $state, $compile, MiscService, TRXNService ) {

	$scope.generateJournalEntryRow = function( row ) {
		return [ 'Journal Entry No.' + row.id, row.id, row.date ];
	}

	$scope.recoverJournalEntry = function( id ) {
		TRXNService.recoverDelete( 'journal_entry', 1 ).then( function( response ) {

		} );
	}

	$scope.initialize = function() {
		$scope.dataTable = CommonFunc().initializeDataTable( '#journalEntriesDataTable', [ "Journal Entry No", "ID", "Date" ], $scope, $compile );

		$( '#journalEntriesDataTable' ).on( 'click', 'tbody tr', function() {
			CommonFunc().goToTransaction( $( this ).find( 'td:nth-child(2)' ).text(), 'new-journal-entry', $state );
		} );

		MiscService.massRetrieve( [ 'journal_entry' ] ).done( function( response ) {
			var content = response.data.content;

			CommonFunc().redrawDataTable( $scope.dataTable, content.journalEntry, $scope.generateJournalEntryRow, 'journal_entry' );
		} );
	}

	$scope.initialize();
}

angular
	.module( 'bookkeeping' )
	.controller( 'JournalEntryCtrl', [ '$scope', '$state', '$compile', 'MiscService', 'TRXNService', JournalEntryCtrl ] );