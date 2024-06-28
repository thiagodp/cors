<?php

use Symfony\Component\HttpClient\HttpClient;

describe( 'cors-real-server-with-default-options', function() {

    beforeAll( function() {

        $server = require( __DIR__ .'/../test-server/server.php' );
        $localServer = $server[ 'domain' ] . ':80';

        $this->server = $localServer;

        // HTTP Server
        $upperDir = dirname( __DIR__ );
        $cmd = "cd $upperDir && " . 'cd test-server && cd default && php -S ' . $this->server;
        $spec = [
            [ 'pipe', 'r' ], // stdin
            [ 'pipe', 'w' ], // stdout
            [ 'pipe', 'w' ], // stderr
        ];
        $this->process = @proc_open( $cmd, $spec, $exitPipes );
        if ( $this->process === false ) {
            throw new Exception( 'Should be able to run the HTTP server.' );
        }

        // HTTP Client
        $this->url = 'http://' . $this->server;
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

        it( 'should return status code 204', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => 'http://different-domain.com'
                ],
                'timeout' => 2
            ] );

            expect( $response->getStatusCode() )->toBe( 204 );
            $headers = $response->getHeaders();
            expect( $headers )->toContainKey( 'access-control-allow-origin' );
            expect( $headers[ 'access-control-allow-origin' ][ 0 ] )->toEqual( 'http://different-domain.com' );
        } );


        it( 'should return the header "Access-Control-Allow-Origin"', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => 'http://different-domain.com'
                ],
                'timeout' => 2
            ] );

            expect( isset( $response->getHeaders()[ 'access-control-allow-origin' ] ) )->toBeTruthy();
        } );


        it( 'should return the header "Access-Control-Allow-Origin" with "*" when Origin is not sent', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'timeout' => 2
            ] );

            $allowedOrigin = $response->getHeaders()[ 'access-control-allow-origin' ][ 0 ];
            expect( $allowedOrigin )->toEqual( '*' );
        } );


        it( 'should return the header "Access-Control-Allow-Origin" with the Origin when it is sent', function() {

            $origin = 'http://different-domain.com';

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => $origin
                ],
                'timeout' => 2
            ] );

            $allowedOrigin = $response->getHeaders()[ 'access-control-allow-origin' ][ 0 ];
            expect( $allowedOrigin )->toEqual( $origin );
        } );

        it( 'should return the header "Access-Control-Allow-Methods" with the value of "Access-Control-Request-Method" when defined', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Access-Control-Request-Method' => 'POST'
                ],
                'timeout' => 2
            ] );

            $headers = $response->getHeaders();
            expect( isset( $headers[ 'access-control-allow-methods' ] ) )->toBeTruthy();
            // expect( $value )->toEqual( 'POST' );
        } );

    } );

} );

?>