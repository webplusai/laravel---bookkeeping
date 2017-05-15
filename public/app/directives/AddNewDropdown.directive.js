'use strict';

function addNewDropdown( $compile, $parse ) {
	var output = {};

	output.require = 'ngModel';

	output.link = function( scope, element, attrs ) {
		$( element ).wrap( '<div class="custom-dropdown print-hidden"> </div>' );
		$( element ).parent().prepend( $compile( '<div class="selectize-input" style="padding: 0px !important;"> <input type="text" tabindex="1" placeholder="' + attrs.placeholder + '" class="dropdown-input" data-ng-model="' + attrs.ngModel + '" style="width: 100%; padding: 10px 10px !important;"> </div>' )( scope ) );
		$( element ).parent().find( '.dropdown-input' ).keyup( function() {

			var UPARROW = 38;
    		var DOWNARROW = 40;
    		var charCode = ( event.which ) ? event.which : event.keyCode;

    		if ( charCode == UPARROW || charCode == DOWNARROW ) {

    			var optionSelector = '.selectize-dropdown-content .option';
    			var itemCount = $( optionSelector ).length;
    			var currentIndex = 0;
    			var nextSelector = 0;

    			for ( var i = 1; i <= itemCount; i++ ) {
    				if ( $( optionSelector + ':nth-child(' + i + ')' ).hasClass( 'selected' ) ) {
    					currentIndex = i;
    					break;
    				}
    			}

    			$( optionSelector ).removeClass( 'selected' );

    			if ( charCode == UPARROW ) {
    				if ( currentIndex == 2 || currentIndex == 0 )
    					nextSelector = optionSelector + ':last-child';
    				else 
    					nextSelector = optionSelector + ':nth-child(' + ( currentIndex - 1 ) + ')';
    			} else if ( charCode == DOWNARROW ) {
    				if ( currentIndex == itemCount || currentIndex == 0 )
    					nextSelector = optionSelector + ':nth-child( 2 )';
    				else
    					nextSelector = optionSelector + ':nth-child(' + ( currentIndex + 1 ) + ')';
    			}
    			
    			$( nextSelector ).addClass( 'selected' );
    			$( this ).val( $( nextSelector ).attr( 'value' ) ).trigger( 'change' );
    		} else {
    			showDropdownList( $(this).parent() );
    		}

		} );
		$( element ).parent().find( '.selectize-input' ).click( function() {
			if ( !$( '.custom-dropdown-list' ).length )
				showDropdownList( this );
			else
				hideDropdownList();

			$( this ).find( '.dropdown-input' ).focus();
		} );
		$( '.selectize-input' ).mouseup( function( event ) {
			event.stopPropagation();
		} );
		$( element ).hide();

		if ( !!attrs.ngModel ) {
			var assign = $parse( attrs.ngModel ).assign;

			element.bind('change', function( e ) {
				if ( attrs.changeEvent )
					scope.$eval( attrs.changeEvent )();
				// assign( { transaction: { customer: element.val() } } );
			} );

			scope.$watch( attrs.ngModel, function( value ) {
				// alert( attrs.ngModel );
				// element.val( value );
				// element.find( '.dropdown-input' ).val( value );
			} );
		}

		function showDropdownList( input ) {
			var dropdown = $( 'div.custom-dropdown-list' );
			if ( !dropdown.length ) {
				dropdown = createDropdown( $( input ).find( '.dropdown-input' )[0] );
			}
			fillDropdown( $( input ).find( '.dropdown-input' )[0] );
			$( element ).siblings( '.selectize-input' ).addClass( 'dropdown-active' );
		}

		function hideDropdownList() {
			$( '.custom-dropdown-list' ).remove();
			$( element ).siblings( '.selectize-input' ).removeClass( 'dropdown-active' );
		}

		function createDropdown( input ) {
			var rect = $( input ).offset();
			$( 'body' ).append( '<div class="custom-dropdown-list selectize-dropdown single addnew slt-autocomplete demo-default" style="position: absolute; top:' + ( rect.top + 35 ) + 'px; left: ' + ( rect.left - 1 ) + 'px; width: ' + $(input).parent().outerWidth() + 'px;' + '"> </div>' );
		}

		function fillDropdown( input ) {
			$( '.custom-dropdown-list *' ).remove();
			$( '.custom-dropdown-list' ).append( '<div class="selectize-dropdown-content"> </div>');
			$( '.selectize-dropdown-content' ).append( '<div class="option addnew" data-selectable> Add New </div>' );
			$( '.selectize-dropdown-content .addnew' ).click( function() {
				$( '.custom-dropdown-list' ).remove();
				scope.$eval( attrs.addNewEvent )( attrs.personDropdownIndex );
			} );

			for ( var i = 0; i < $( element ).find( "option" ).length; i++ ) {
				var value = $( element ).find( "option" )[i].value;
				var label = $( element ).find( "option" )[i].label;

				if (value == '?') 
					continue;

				if ( input.value == "" || $( element ).find( "option" )[i].value.toLowerCase().indexOf( input.value.toLowerCase() ) != -1 ) {
					if ( attrs.customerSupplierDropdown != undefined )
						$( '.selectize-dropdown-content' ).append( '<div class="option" value="' + value + '" data-selectable>' + value + '<span>' + label + '</span></div>' );
					else
						$( '.selectize-dropdown-content' ).append( '<div class="option" value="' + value + '" data-selectable>' + value + '</div>' );
				}
			}

			$( '.selectize-dropdown-content div:not(.addnew)' ).click( function() {
				input.value = $(this).attr( 'value' );
				$( element ).val( input.value ).trigger( 'change' );
				$( '.custom-dropdown-list' ).remove();
			} );

			$( document ).mouseup( function ( e ) {
		      	var container = $( '.custom-dropdown-list' );
		      	if ( !container.is( e.target ) && container.has( e.target ).length === 0 ) {
		      		$( '.selectize-input' ).removeClass( 'dropdown-active' );
		        	container.remove();
		      	}
		    } );
		}
	}

	return output;
}

angular
	.module( 'bookkeeping' )
	.directive( 'addNewDropdown', [ '$compile', '$parse', addNewDropdown ] );