<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;

const NOT_ALLOWED_ORIGIN = 'http://different-domain.com';

describe( 'server with origin', function() {

    $config = require_once( __DIR__ . '/config.php' );
    $this->allowed = $config[ 'allowed' ];

    $rootDir = dirname( __FILE__ );
    $this->server = $config[ 'domain' ] . ':' . $config[ 'port' ];
    $this->process = null;

    beforeAll( function() use ( $rootDir ) {

        // HTTP Server
        $cmd = "cd $rootDir && php -S {$this->server}";
        echo 'Running server: ' . $cmd, PHP_EOL;

        $spec = [
            [ 'pipe', 'r' ], // stdin
            [ 'pipe', 'w' ], // stdout
            [ 'pipe', 'w' ], // stderr
        ];
        $this->process = @proc_open( $cmd, $spec, $exitPipes );
        if ( $this->process === false ) {
            throw new Exception( 'Cannot run the HTTP server.' );
        }

        // URL
        $this->url = 'http://' . $this->server;

        // HTTP Client
        $this->client = HttpClient::create();
    } );


    afterAll( function() {
        $this->client = null;

        if ( $this->process === false ) {
            return;
        }
        $exitCode = proc_terminate( $this->process ) ? 0 : -1;
        if ( $exitCode < 0 ) {
            throw new Exception( 'Cannot close the HTTP server.' );
        }
    } );


    describe( 'preflight', function() {

        it( 'should return status code 204 for an allowed origin', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => $this->allowed
                ],
                'timeout' => 2
            ] );

            expect( $response->getStatusCode() )->toBe( 204 );
        } );


        it( 'should return the status code 403 (Forbidden) when the origin is not allowed', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => NOT_ALLOWED_ORIGIN
                ],
                'timeout' => 2
            ] );

            // var_dump( $response->getHeaders() );

            expect( $response->getStatusCode() )->toBe( 403 );
        } );


        it( 'should return the status code 403 (Forbidden) when the origin is not allowed', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => NOT_ALLOWED_ORIGIN
                ],
                'timeout' => 2

            ] );

            expect( $response->getStatusCode() )->toBe( 403 );
        } );


        it( 'should return the first allowed origin when the origin is not sent', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'timeout' => 2
            ] );

            // getHeaders( false ) to avoid throwing an exception when a 3xx, 4xx or 5xx code is returned !
            $responseOrigin = ( $response->getHeaders( false )[ 'access-control-allow-origin' ] ?? [ null ] ) [ 0 ];
            expect( $responseOrigin )->toEqual( $this->server );
        } );

        it( 'should return status Forbidden the origin is not sent', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'timeout' => 2
            ] );
            expect( $response->getStatusCode() )->toBe( 403 );
        } );


        it( 'should return the first allowed origin when the sent origin is not allowed', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => NOT_ALLOWED_ORIGIN
                ],
                'timeout' => 2
            ] );

            $responseOrigin = ( $response->getHeaders( false )[ 'access-control-allow-origin' ] ?? [ null ] ) [ 0 ];
            expect( $responseOrigin )->toEqual( $this->server );
        } );

    } );


    it( 'should answer a PUT correctly', function() {

        $response = $this->client->request( 'PUT', $this->url . '/example', [
            'headers' => [
                'Origin' => $this->allowed
            ],
            'timeout' => 2
        ] );

        expect( $response->getStatusCode() )->toBe( 200 );

        $responseOrigin = ( $response->getHeaders( false )[ 'access-control-allow-origin' ] ?? [ null ] ) [ 0 ];
        expect( $responseOrigin )->toEqual( $this->allowed );
    } );

} );

?>