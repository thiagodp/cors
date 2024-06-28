<?php
require_once __DIR__ . '/../vendor/autoload.php';

use phputil\router\Router;
use function phputil\cors\cors;

$router = new Router();

$router->use( cors( [ 'origin' => [ '127.0.0.1:8889', 'allowed.com' ] ] ) );

$router->get( '/', function( $req ) {
    $req->send( 'Hello' );
} );

$router->listen();
?>