<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;

describe( 'server with default config', function() {

    $port = '9997';
    $host = PHP_OS_FAMILY === 'Windows' ? 'localhost' : '0.0.0.0';

    $this->server = "$host:$port";
    $this->url = 'http://' . $this->server;
    $this->process = null;

    beforeAll( function() {

        $rootDir = dirname( __FILE__ );

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

        it( 'should return status code 204', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => 'http://different-domain.com'
                ],
                'timeout' => 2
            ] );

            expect( $response->getStatusCode() )->toBe( 204 );
        } );


        it( 'should reflect the origin that was sent', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Origin' => 'http://different-domain.com'
                ],
                'timeout' => 2
            ] );

            $responseOrigin = ( $response->getHeaders( false )[ 'access-control-allow-origin' ] ?? [ '' ] ) [ 0 ];
            expect( $responseOrigin )->toEqual( 'http://different-domain.com' );
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


        it( 'should return the origin "*" when Origin is not sent', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'timeout' => 2
            ] );

            $responseOrigin = ( $response->getHeaders( false )[ 'access-control-allow-origin' ] ?? [ '' ] ) [ 0 ];
            expect( $responseOrigin )->toEqual( '*' );
        } );

        it( 'should return the method that was sent', function() {

            $response = $this->client->request( 'OPTIONS', $this->url, [
                'headers' => [
                    'Access-Control-Request-Method' => 'POST'
                ],
                'timeout' => 2
            ] );

            $responseMethods = ( $response->getHeaders( false )[ 'access-control-allow-methods' ] ?? [ '' ] ) [ 0 ];
            expect( $responseMethods )->toEqual( 'POST' );
        } );

    } );

    it( 'should answer a PUT request correctly', function() {

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