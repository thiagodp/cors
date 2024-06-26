<?php

use Symfony\Component\HttpClient\HttpClient;

describe( 'cors-real', function() {

    beforeAll( function() {

        $server = '127.0.0.1:8888';

        // HTTP Server
        $cmd = 'cd test-demo && php -S ' . $server;
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
        $this->url = 'http://' . $server;
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


    describe( 'default preflight', function() {

        it( 'should return status code 204', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => 'http://different-domain.com'
                ],
                'timeout' => 2
            ] );

            expect( $response->getStatusCode() )->toBe( 204 );
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

            $value = $response->getHeaders()[ 'access-control-allow-methods' ][ 0 ];
            expect( $value )->toEqual( 'POST' );
        } );        

    } );

} );

?>