'use strict';

angular
    .module( 'bookkeeping', 
        [
            'ui.router',
            'ngAnimate',
            'ui.bootstrap',
            'oc.lazyLoad',
            'ngStorage',
            'ngSanitize',
            'ui.utils',
            'ngTouch',
            'ui.sortable',
        ]
    );

angular.module( 'bookkeeping' )
    .factory( 'authInterceptor', [ '$location', function ( $location ) {
        var interceptorFactory = {};
        var _request = function ( config ) {
            config.headers = config.headers || {};
            config.headers[ 'Authorization' ] = 'Bearer ' + sessionStorage.access_token;
            return config;
        }

        interceptorFactory.request = _request;
        return interceptorFactory;
    }
] );

angular.module( 'bookkeeping' )
    .factory( 'securityInterceptor', [ '$q', '$injector', function( $q, $injector ) {
        return {
            responseError: function( response ) {
                if ( response.status === 401 ) {
                    $injector.get( '$state' ).go( 'app.user.signin' );
                    return $q.reject( response );
                }
                else {
                    return $q.reject( response );
                }
            }
        }
    }
] );

angular.module( 'bookkeeping' ).config( function( $httpProvider ) {
    $httpProvider.interceptors.push( 'authInterceptor' );
    $httpProvider.interceptors.push( 'securityInterceptor' );
} );

angular.module( 'bookkeeping' ).run( function( $rootScope, $templateCache ) {

    $rootScope.$on( '$viewContentLoaded', function() {
        //CommonFunc().showPreloader( '.full-screen-loader' );
        $templateCache.removeAll();
    } );

} );