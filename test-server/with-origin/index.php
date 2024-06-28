<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use phputil\router\Router;
use function phputil\cors\cors;

$router = new Router();

$domain = require( __DIR__ .'/../domain.php' );

$router->use( cors( [ 'origin' => [ $domain . ':8889', 'allowed.com' ] ] ) );

$router->get( '/', function( $req ) {
    $req->send( 'Hello' );
} );

$router->listen();
?>