<?php
require_once 'src/cors.php';

use phputil\cors\CorsOptions;

describe( 'CorsOptions', function() {

    describe( 'fromArray', function() {

        it( 'does not change an initial value when the options is not defined', function() {
            $c = ( new CorsOptions() )->fromArray( [] );
            expect( $c->origin )->toBe( '*' );
        } );

        it( 'changes an initial value when the options is defined', function() {
            $c = ( new CorsOptions() )->fromArray( [ 'origin' => 'https://foo.com' ] );
            expect( $c->origin )->toBe( 'https://foo.com' );
        } );

    } );
} );

?>