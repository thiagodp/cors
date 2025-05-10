<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

use phputil\router\Router;
use function phputil\cors\cors;

$router = new Router();

$config = require_once( __DIR__ . '/config.php' );
$localServer = $config[ 'domain' ] . ':' .  $config[ 'port' ];
$allowed = $config[ 'allowed' ];

$options = [ 'origin' => [ $localServer, $allowed ] ];

$router->use( cors( $options ) );

$router->get( '/', function( $req, $res ) use ( $options ) {
    $res->send( 'Hello. cors() options are: <br/><br/><code>' . json_encode( $options ) . '</code>' );
} );

$router->put( '/example', function( $req, $res ) {
    $res->send( 'OK' );
} );

$router->listen();
?>