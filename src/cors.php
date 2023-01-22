<?php
namespace phputil\cors;

/**
 * This file is inspired by Troy Goode's CORS Router for ExpressJS,
 * available at https://github.com/expressjs/cors with a MIT License.
 *
 * You can see the lastest CORS standard at https://fetch.spec.whatwg.org/#cors-protocol
 */

require_once 'cors-options.php';
require_once 'http.php';

const ANY = '*';

const HEADER_VARY = 'Vary';
const HEADER_ORIGIN = 'Origin'; // URL
const HEADER_CREDENTIAIS = 'Credentials'; // 'omit', 'include', 'same-origin'
const HEADER_CONTENT_LENGTH = 'Content-Length';

// CORS Request Headers
const HEADER_ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';

// CORS Response Headers
const HEADER_ACCESS_CONTROL_ALLOW_ORIGIN        = 'Access-Control-Allow-Origin';      // URL, 'null' or '*'
const HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS   = 'Access-Control-Allow-Credentials'; // 'include', 'same-origin'
const HEADER_ACCESS_CONTROL_ALLOW_METHODS       = 'Access-Control-Allow-Methods';
const HEADER_ACCESS_CONTROL_ALLOW_HEADERS       = 'Access-Control-Allow-Headers';
const HEADER_ACCESS_CONTROL_MAX_AGE             = 'Access-Control-Max-Age'; // Default is 5
const HEADER_ACCESS_CONTROL_EXPOSE_HEADERS      = 'Access-Control-Expose-Headers';

/**
 * CORS middleware.
 *
 * @param array|CorsOptions $options CORS options.
 * @return callable
 */
function cors( $options = [] ) {

    $opt = is_array( $options )
        ? ( new CorsOptions() )->fromArray( $options )
        : ( ( is_object( $options ) && ( $options instanceof CorsOptions ) )
            ? $options : new CorsOptions() );

    return function ( &$req, &$res, &$stop ) use ( &$opt ) {

        $headers = [];
        makeOrigin( $req, $opt, $headers );
        makeCredentials( $opt, $headers );

        if ( $req->method() === METHOD_OPTIONS ) { // Preflight Request

            makeMethods( $opt, $headers );
            makeAllowedHeaders( $opt, $headers );
            makeExposedHeaders( $opt, $headers );
            makeMaxAge( $opt, $headers );

            $res->header( $headers );

            if ( $opt->preflightContinue ) { // Don't stop
                return;
            }

            $res->status( $opt->optionsSuccessStatus )->header( HEADER_CONTENT_LENGTH, 0 )->end();
            $stop = true;
        } else {
            makeOrigin( $req, $opt, $headers );
            makeCredentials( $opt, $headers );

            $res->header( $headers );
        }
    };
}


function isOriginAllowed( $requestOrigin, $originToCheck ) {
    if ( $requestOrigin === $originToCheck ) {
        return true;
    }
    if ( \is_array( $originToCheck ) ) {
        foreach ( $originToCheck as $origin ) {
            if ( isOriginAllowed( $requestOrigin, $origin ) ) {
                return true;
            }
        }
    }
    return false;
}


function makeOrigin( &$req, CorsOptions &$opt, array &$headers ) {
    $value = $opt->origin;
    if ( empty( $value ) ) {
        $value = ANY;
    }
    if ( \is_string( $value ) ) {
        $headers[ HEADER_ACCESS_CONTROL_ALLOW_ORIGIN ] = $value;
    } else if ( \is_array( $value ) ) {
        // Since only a string can be set as a value, it will try to check if the request Origin
        // is included in the array. If it is included, then it will be used. Otherwise, it will be
        // not included in the response.
        $requestOrigin = $req->header( HEADER_ORIGIN );
        if ( ! isset( $requestOrigin ) ) {
            if ( isset( $headers[ HEADER_ACCESS_CONTROL_ALLOW_ORIGIN ] ) ) {
                unset( $headers[ HEADER_ACCESS_CONTROL_ALLOW_ORIGIN ] );
            }
            return;
        }
        if ( isOriginAllowed( $requestOrigin, $opt->origin ) ) { // Same origin?
            $headers[ HEADER_ACCESS_CONTROL_ALLOW_ORIGIN ] = $requestOrigin; // Reflect origin
        }
    }
    // Add Vary header
    if ( $value != ANY ) {
        $headers[ HEADER_VARY ] = HEADER_ORIGIN;
    }
}


function makeCredentials( CorsOptions &$opt, array &$headers ) {
    if ( $opt->credentials ) {
        $headers[ HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS ] = 'true';
    }
}


function makeMethods( CorsOptions &$opt, array &$headers ) {
    $headers[ HEADER_ACCESS_CONTROL_ALLOW_METHODS ] = \is_array( $opt->methods )
        ? \implode( ',', $opt->methods ) : $opt->methods;
}


function makeAllowedHeaders( CorsOptions &$opt, array &$headers ) {
    if ( ! \is_array( $opt->allowedHeaders ) || empty( $opt->allowedHeaders ) ) {
        $headers[ HEADER_VARY ] = HEADER_ACCESS_CONTROL_REQUEST_HEADERS;
        return;
    }
    $headers[ HEADER_ACCESS_CONTROL_REQUEST_HEADERS ] = \implode( ',', $opt->allowedHeaders );
}


function makeExposedHeaders( CorsOptions &$opt, array &$headers ) {
    if ( ! is_array( $opt->exposedHeaders ) || empty( $opt->exposedHeaders ) ) {
        return;
    }
    $headers[ HEADER_ACCESS_CONTROL_EXPOSE_HEADERS ] = implode( ',', $opt->exposedHeaders );
}


function makeMaxAge( CorsOptions &$opt, array &$headers ) {
    if ( ! is_numeric( $opt->maxAge ) ) {
        return;
    }
    $headers[ HEADER_ACCESS_CONTROL_MAX_AGE ] = $opt->maxAge;
}

?>
