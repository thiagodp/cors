<?php
require_once 'vendor/phputil/router/src/FakeHttpRequest.php';
require_once 'vendor/phputil/router/src/FakeHttpResponse.php';
// require_once 'src/cors.php'; // DO NOT include it, since kahlan-config.php already does it.

use \phputil\router\FakeHttpRequest;
use \phputil\router\FakeHttpResponse;

use function phputil\cors\cors;

describe( 'cors-fake', function() {

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

    it( 'should stop a Preflight request', function() {
        $fn = cors();
        $this->req->withMethod( 'OPTIONS' );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        expect( $stop )->toBeTruthy();
    } );

    it( 'should NOT stop a Preflight request when the option "preflightContinue" is turned on', function() {
        $fn = cors( [ 'preflightContinue' => true ] );
        $this->req->withMethod( 'OPTIONS' );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        expect( $stop )->toBeFalsy();
    } );


    describe( 'by default', function() {

        it( 'includes the header "Access-Control-Allow-Origin" with requested "Origin" as value', function() {
            $fn = cors();
            $origin = 'foo.com';
            $this->req->withHeader( 'Origin', $origin );
            $stop = false;
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Allow-Origin' );
            expect( $value )->toBe( $origin );
        } );

        it( 'includes the header "Vary" with the value "Origin"', function() {
            $fn = cors();
            $this->req->withHeader( 'Origin', 'foo.com' );
            $stop = false;
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Vary' );
            expect( $value )->toBe( 'Origin' );
        } );

        it( 'includes the header "Access-Control-Allow-Credentials" with the value "true"', function() {
            $fn = cors();
            $this->req->withHeader( 'Origin', 'foo.com' );
            $stop = false;
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Allow-Credentials' );
            expect( $value )->toBe( 'true' );
        } );

        it( 'includes the header "Access-Control-Allow-Methods" with the usual HTTP methods as value, when the header "Access-Control-Request-Method" is not defined', function() {
            $fn = cors();
            $this->req->withMethod( 'OPTIONS' );
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

        it( 'includes the header "Access-Control-Allow-Methods" with the value of "Access-Control-Request-Method" when received and the option "methods" is not defined', function() {
            $fn = cors();
            $this->req->withMethod( 'OPTIONS' );
            $this->req->withHeader( 'Origin', 'foo.com' );
            $this->req->withHeader( 'Access-Control-Request-Method', 'POST' );
            $stop = false;
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Allow-Methods' );
            expect( $value )->toContain( 'POST' );
            expect( $value )->not->toContain( 'GET' );
            expect( $value )->not->toContain( 'HEAD' );
            expect( $value )->not->toContain( 'OPTIONS' );
            expect( $value )->not->toContain( 'PUT' );
            expect( $value )->not->toContain( 'DELETE' );
            expect( $value )->not->toContain( 'PATCH' );
        } );


        it( 'includes the header "Access-Control-Allow-Methods" with the value of the option "methods" even when "Access-Control-Request-Method" is defined', function() {
            $fn = cors( [ 'methods' => 'PUT,DELETE' ] );
            $this->req->withMethod( 'OPTIONS' );
            $this->req->withHeader( 'Origin', 'foo.com' );
            $this->req->withHeader( 'Access-Control-Request-Method', 'POST' );
            $stop = false;
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Allow-Methods' );
            expect( $value )->toContain( 'PUT' );
            expect( $value )->toContain( 'DELETE' );
            expect( $value )->not->toContain( 'GET' );
            expect( $value )->not->toContain( 'HEAD' );
            expect( $value )->not->toContain( 'OPTIONS' );
            expect( $value )->not->toContain( 'PATCH' );
        } );


        it( 'returns the status 204 in a Preflight request', function() {
            $this->req->withMethod( 'OPTIONS' );
            $stop = false;
            $fn = cors();
            $fn( $this->req, $this->res, $stop );
            expect( $this->res->isStatus( 204 ) )->toBeTruthy();
        } );

        it( 'returns the header "Content-Length" with the value "0" in a Preflight request', function() {
            $this->req->withMethod( 'OPTIONS' );
            $stop = false;
            $fn = cors();
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Content-Length' );
            expect( $value )->toEqual( '0' );
        } );

        it( 'does not include the header "Access-Control-Max-Age"', function() {
            $stop = false;
            $fn = cors();
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Max-Age' );
            expect( $value )->toBeNull();
        } );

        it( 'does not include the header "Access-Control-Expose-Headers"', function() {
            $stop = false;
            $fn = cors();
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Expose-Headers' );
            expect( $value )->toBeNull();
        } );

    } ); // by default


    describe('with origin', function() {

        it( 'should include the header "Access-Control-Allow-Origin" with the first origin when the "Origin" header is not sent.'
        , function() {
            $fn = cors( [ 'origin' => [ 'foo.com', 'bar.com' ] ] );
            $stop = false;
            $fn( $this->req, $this->res, $stop );
            $value = $this->res->getHeader( 'Access-Control-Allow-Origin' );
            expect( $value )->toBe( 'foo.com' );
        } );
    } );


    it( 'should include the header "Access-Control-Allow-Methods" when the option "methods" is defined', function() {
        $fn = cors( [ 'methods' => 'GET,POST' ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Methods' );
        expect( $value )->toBe( 'GET,POST' );
    } );

    it( 'should include the header "Access-Control-Allow-Headers" when the option "allowedHeaders" is defined', function() {
        $fn = cors( [ 'allowedHeaders' => [ 'Accept', 'Cookie' ] ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Headers' );
        expect( $value )->toBe( 'Accept,Cookie' );
    } );

    it( 'should include the header "Access-Control-Expose-Headers" when the option "exposedHeaders" is defined', function() {
        $fn = cors( [ 'exposedHeaders' => [ 'X-One', 'X-Box' ] ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Expose-Headers' );
        expect( $value )->toBe( 'X-One,X-Box' );
    } );

    it( 'should include the header "Access-Control-Allow-Origin" when it receives the header "Origin" with a value that is in the list defined in the option "origin"', function() {
        $origin = 'bar.org';
        $this->req->withHeader( 'Origin', $origin );
        $fn = cors( [ 'origin' => [ 'foo.com', $origin ] ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Origin' );
        expect( $value )->toBe( $origin );
    } );

    it( 'should include the header "Access-Control-Allow-Origin" with the first origin when it receives the header "Origin" with a value that is NOT in the list defined in the option "origin"', function() {
        $origin = 'other.com';
        $this->req->withHeader( 'Origin', $origin );
        $fn = cors( [ 'origin' => [ 'foo.com', 'bar.org' ] ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Origin' );
        expect( $value )->toBe( 'foo.com' );
    } );

    it( 'should include the header "Access-Control-Allow-Origin" when it receives the header "Origin" with a value that is that as same the defined in the option "origin"', function() {
        $origin = 'bar.org';
        $this->req->withHeader( 'Origin', $origin );
        $fn = cors( [ 'origin' => $origin ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Origin' );
        expect( $value )->toBe( $origin );
    } );

    it( 'should include the header "Access-Control-Allow-Origin" with the first allowed origin when it receives the header "Origin" with a value that is NOT the same as the defined in the option "origin"', function() {
        $origin = 'bar.org';
        $this->req->withHeader( 'Origin', $origin );
        $fn = cors( [ 'origin' => 'foo.com' ] );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        $value = $this->res->getHeader( 'Access-Control-Allow-Origin' );
        expect( $value )->toBe( 'foo.com' );
    } );

} );
