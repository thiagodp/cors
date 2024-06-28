<?php

use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\HttpClient;

describe( 'cors-real-server-with-origin', function() {

    beforeAll( function() {

        $server = '127.0.0.1:8889';

        // HTTP Server
        $cmd = 'cd test-server-with-origin && php -S ' . $server;
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
        $this->url = 'http://' . $server;

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

        it( 'should return status code 204 for the same origin', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'timeout' => 2
            ] );

            expect( $response->getStatusCode() )->toBe( 204 );
        } );


        it( 'should return the status code 403 (Forbidden) when the origin is not allowed!', function() {
        
            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => 'http://different-domain.com'
                ],
                'timeout' => 2                   

            ] );

            expect( $response->getStatusCode() )->toBe( 403 );

            $headers = $response->getHeaders( false ); // false to avoid throwing an exception when a 3xx, 4xx or 5xx code is returned !
            expect( $headers )->toContainKey( 'access-control-allow-origin' );
            expect( $headers[ 'access-control-allow-origin' ][ 0 ] )->toEqual( 'false' );            
        } );        


        it( 'should return the header "Access-Control-Allow-Origin" with "*" when Origin is not sent', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'timeout' => 2
            ] );

            $allowedOrigin = $response->getHeaders()[ 'access-control-allow-origin' ][ 0 ];
            expect( $allowedOrigin )->toEqual( '*' );
        } );     

    } );

} );

?>