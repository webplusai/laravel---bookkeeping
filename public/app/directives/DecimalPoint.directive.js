angular
    .module( 'bookkeeping' )
    .directive( 'decimalPoint',function() {
    return {
        link: function( scope,ele,attrs ) {
            ele.bind( 'keypress',function( e ) {
                var newVal = $( this ).val() + ( e.charCode !== 0 ? String.fromCharCode( e.charCode ) : '' );
                if( $( this ).val().search( /(.*)\.[0-9][0-9]/ ) === 0 && newVal.length > $( this ).val().length ){
                    e.preventDefault();
                }
            } );
        }
    };
} );