<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use phputil\router\Router;
use function phputil\cors\cors;

$router = new Router();

$router->use( cors() );

$router->get( '/', function( $req, $res ) {
    $res->send( 'Hello' );
} );

$router->put( '/example', function( $req, $res ) {
    $res->send( 'OK' );
} );

$router->listen();
?>