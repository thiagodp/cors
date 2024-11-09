<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use phputil\router\Router;
use function phputil\cors\cors;

$router = new Router();

$server = require( __DIR__ .'/../server.php' );
$localServer = $server[ 'domain' ] . ':' .  $server[ 'port' ];

$router->use( cors( [ 'origin' => [ $localServer, 'allowed.com' ] ] ) );

$router->get( '/', function( $req ) {
    $req->send( 'Hello' );
} );

$router->put( '/example', function( $req ) {
    $req->send( 'OK' );
} );

$router->listen();
?>