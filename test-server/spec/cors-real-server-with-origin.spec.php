<?php

use Symfony\Component\HttpClient\HttpClient;

describe( 'cors-real-server-with-origin', function() {

    $rootDir = dirname( __DIR__, 2 );
    $server = require( $rootDir . '/test-server/server.php' );
    $localServer = $server[ 'domain' ] . ':' . $server[ 'port' ];

    $this->server = $localServer;
    $this->process = null;

    beforeAll( function() use ( $rootDir ) {

        // HTTP Server
        $cmd = "cd $rootDir && " . 'cd test-server && cd with-origin && php -S ' . $this->server;
        $spec = [
            [ 'pipe', 'r' ], // stdin
            [ 'pipe', 'w' ], // stdout
            [ 'pipe', 'w' ], // stderr
        ];
        $this->process = @proc_open( $cmd, $spec, $exitPipes );
        if ( $this->process === false ) {
            throw new Exception( 'Should be able to run the HTTP server.' );
        }

        // URL
        $this->url = 'http://' . $this->server;

        // HTTP Client
        $this->client = HttpClient::create();
    } );


    afterAll( function() {
        $this->cliente = null;

        if ( $this->process === false ) {
            return;
        }
        $exitCode = proc_terminate( $this->process ) ? 0 : -1;
        if ( $exitCode < 0 ) {
            throw new Exception( 'Should be able to close the HTTP server.' );
        }
    } );


    describe( 'preflight', function() {

        it( 'should return status code 204 for an allowed origin', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => 'allowed.com'
                ],
                'timeout' => 2
            ] );

            expect( $response->getStatusCode() )->toBe( 204 );
        } );


        it( 'should return the status code 403 (Forbidden) when the origin is not allowed', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => 'http://different-domain.com'
                ],
                'timeout' => 2
            ] );

            // var_dump( $response->getHeaders() );

            expect( $response->getStatusCode() )->toBe( 403 );
        } );


        it( 'should return the status code 403 (Forbidden) when the origin is not allowed', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => 'http://different-domain.com'
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
                    'Origin' => 'http://different-domain.com'
                ],
                'timeout' => 2
            ] );

            $responseOrigin = ( $response->getHeaders( false )[ 'access-control-allow-origin' ] ?? [ null ] ) [ 0 ];
            expect( $responseOrigin )->toEqual( $this->server );
        } );

    } );


    it( 'PUT should answer correctly', function() {

        $response = $this->client->request( 'PUT', $this->url . '/example', [
            'headers' => [
                'Origin' => 'http://different-domain.com'
            ],
            'timeout' => 2
        ] );

        expect( $response->getStatusCode() )->toBe( 200 );
    } );

} );

?>