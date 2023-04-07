<?php
require_once 'vendor/phputil/router/src/FakeHttpRequest.php';
require_once 'vendor/phputil/router/src/FakeHttpResponse.php';
// require_once 'src/cors.php'; // DO NOT include it, since kahlan-config.php already does it.

use \phputil\router\FakeHttpRequest;
use \phputil\router\FakeHttpResponse;

use function phputil\cors\cors;
use phputil\cors\CorsOptions;

describe( 'cors', function() {

    $this->req = null;
    $this->res = null;

    beforeEach( function() {
        $this->req = new FakeHttpRequest();
        $this->res = new FakeHttpResponse();
    } );

    afterEach( function() {
        $this->req = null;
        $this->res = null;
    } );

    it( 'should stop when a preflight is sent', function() {
        $fn = cors();
        $this->req->withMethod( 'OPTIONS' );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        expect( $stop )->toBeTruthy();
    } );

    it( 'should NOT stop when a preflight is sent but "preflightContinue" is on', function() {
        $fn = cors( [ 'preflightContinue' => true ] );
        $this->req->withMethod( 'OPTIONS' );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        expect( $stop )->toBeFalsy();
    } );


    describe( 'by default', function() {

        it( 'includes the request Origin as allowed', function() {
            $fn = cors();
            $origin = 'foo.com';
            $this->req->withHeader( 'Origin', $origin );
            $stop = false;
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Allow-Origin' );
            expect( $value )->toBe( $origin );
        } );

        it( 'includes a Vary header with Origin', function() {
            $fn = cors();
            $this->req->withHeader( 'Origin', 'foo.com' );
            $stop = false;
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Vary' );
            expect( $value )->toBe( 'Origin' );
        } );

        it( 'includes a Credentials header with true', function() {
            $fn = cors();
            $this->req->withHeader( 'Origin', 'foo.com' );
            $stop = false;
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Allow-Credentials' );
            expect( $value )->toBe( 'true' );
        } );

        it( 'includes usual HTTP methods as allowed', function() {
            $fn = cors();
            $this->req->withHeader( 'Origin', 'foo.com' );
            $stop = false;
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Allow-Methods' );
            expect( $value )->toContain( 'GET' );
            expect( $value )->toContain( 'HEAD' );
            expect( $value )->toContain( 'OPTIONS' );
            expect( $value )->toContain( 'POST' );
            expect( $value )->toContain( 'PUT' );
            expect( $value )->toContain( 'DELETE' );
            expect( $value )->toContain( 'PATCH' );
        } );


        it( 'returns status 204 in a Preflight', function() {
            $this->req->withMethod( 'OPTIONS' );
            $stop = false;
            $fn = cors();
            $fn( $this->req, $this->res, $stop );
            expect( $this->res->isStatus( 204 ) )->toBeTruthy();
        } );

        it( 'returns Content-Length 0 in a Preflight', function() {
            $this->req->withMethod( 'OPTIONS' );
            $stop = false;
            $fn = cors();
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Content-Length' );
            expect( $value )->toBe( 0 );
        } );

        it( 'does not include Max Age', function() {
            $stop = false;
            $fn = cors();
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Max-Age' );
            expect( $value )->toBeNull();
        } );

        it( 'does not include Expose Headers', function() {
            $stop = false;
            $fn = cors();
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Expose-Headers' );
            expect( $value )->toBeNull();
        } );

    } );


    it( 'should include Allow Methods when defined', function() {
        $fn = cors( [ 'methods' => 'GET,POST' ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Methods' );
        expect( $value )->toBe( 'GET,POST' );
    } );

    it( 'should include Allow Headers when defined', function() {
        $fn = cors( [ 'allowedHeaders' => [ 'Accept', 'Cookie' ] ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Headers' );
        expect( $value )->toBe( 'Accept,Cookie' );
    } );

    it( 'should include Expose Headers when defined', function() {
        $fn = cors( [ 'exposedHeaders' => [ 'X-One', 'X-Box' ] ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Expose-Headers' );
        expect( $value )->toBe( 'X-One,X-Box' );
    } );

    it( 'should include the Origin when the origin is in the list', function() {
        $origin = 'bar.org';
        $this->req->withHeader( 'Origin', $origin );
        $fn = cors( [ 'origin' => [ 'foo.com', $origin ] ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Origin' );
        expect( $value )->toBe( $origin );
    } );

    it( 'should include \'false\' as the Origin when it is NOT in the list', function() {
        $origin = 'none.com';
        $this->req->withHeader( 'Origin', $origin );
        $fn = cors( [ 'origin' => [ 'foo.com', 'bar.org' ] ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Origin' );
        expect( $value )->toBe( 'false' );
    } );


    it( 'should include the Origin when it is the defined one', function() {
        $origin = 'bar.org';
        $this->req->withHeader( 'Origin', $origin );
        $fn = cors( [ 'origin' => $origin ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Origin' );
        expect( $value )->toBe( $origin );
    } );

    it( 'should include \'false\' as the Origin when it is NOT the defined one', function() {
        $origin = 'bar.org';
        $this->req->withHeader( 'Origin', $origin );
        $fn = cors( [ 'origin' => 'foo.com' ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Origin' );
        expect( $value )->toBe( 'false' );
    } );

} );
