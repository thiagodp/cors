<?php
require_once 'vendor/phputil/router/src/fake-http-request.php';
require_once 'vendor/phputil/router/src/fake-http-response.php';
require_once 'src/cors.php';

use function phputil\cors\cors;
use phputil\cors\CorsOptions;

use \phputil\router\FakeHttpRequest;
use \phputil\router\FakeHttpResponse;

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

    it( 'should stop when HTTP OPTIONS is sent', function() {
        $fn = cors();
        $this->req->withMethod( 'OPTIONS' );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        expect( $stop )->toBeTruthy();
    } );

    it( 'should continue when HTTP OPTIONS is sent but options indicates to go on', function() {
        $fn = cors( ( new CorsOptions() )->withPreflightContinue( true ) );
        $this->req->withMethod( 'OPTIONS' );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        expect( $stop )->toBeFalsy();
    } );

    it( 'accepts options from an array', function() {
        $fn = cors( [ 'preflightContinue' => true ] );
        $this->req->withMethod( 'OPTIONS' );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        expect( $stop )->toBeFalsy();
    } );

    it( 'accepts methods as an array', function() {
        $fn = cors( ( new CorsOptions() )->withMethods( [ 'GET', 'POST' ] ) );
        $this->req->withMethod( 'POST' );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        // $this->res->h
        expect( $stop )->toBeFalsy();
    } );

    it( 'accepts methods as string', function() {
        $fn = cors( ( new CorsOptions() )->withMethods( 'GET,POST' ) );
        $this->req->withMethod( 'POST' );
        $stop = false;
        $fn( $this->req, $this->res, $stop );
        expect( $stop )->toBeFalsy();
    } );

} );
