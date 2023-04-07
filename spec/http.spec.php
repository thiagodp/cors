<?php
require_once 'src/http.php';

use function phputil\cors\isOriginAllowed;

describe( 'http', function() {

    describe( 'isOriginAllowed', function() {

        it( 'allows equal string values', function() {
            $r = isOriginAllowed( 'a', 'a' );
            expect( $r )->toBeTruthy();
        } );

        it( 'allows equal array values', function() {
            $r = isOriginAllowed( [ 'a', 'b' ], [ 'a', 'b' ] );
            expect( $r )->toBeTruthy();
        } );

        it( 'allows one string value inside an array', function() {
            $r = isOriginAllowed( 'a', [ 'a', 'b' ] );
            expect( $r )->toBeTruthy();
        } );

        it( 'does not allow a value that is not in the array', function() {
            $r = isOriginAllowed( 'x', [ 'a', 'b' ] );
            expect( $r )->toBeFalsy();
        } );

    } );

} );

?>