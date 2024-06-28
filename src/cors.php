<?php
namespace phputil\cors;

use phputil\router\HttpRequest;
use phputil\router\HttpResponse;

require_once __DIR__ . '/CorsOptions.php';
require_once __DIR__ . '/http.php';

use function implode;
use function is_array;
use function is_object;
use function is_null;
use function is_numeric;
use function is_string;

/**
 * @see Lastest CORS standard at https://fetch.spec.whatwg.org/#cors-protocol
 */

const HEADER_VARY = 'Vary';
const HEADER_ORIGIN = 'Origin'; // URL
const HEADER_CREDENTIAIS = 'Credentials'; // 'omit', 'include', 'same-origin'
const HEADER_CONTENT_LENGTH = 'Content-Length';

const NOT_ALLOWED_ORIGIN = 'null';

// CORS Request Headers
const REQUEST_HEADER__ACCESS_CONTROL_REQUEST_METHOD     = 'Access-Control-Request-Method';
const REQUEST_HEADER__ACCESS_CONTROL_REQUEST_HEADERS    = 'Access-Control-Request-Headers';

// CORS Response Headers
const HEADER_ACCESS_CONTROL_ALLOW_ORIGIN        = 'Access-Control-Allow-Origin';      // URL, 'null' or '*'
const HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS   = 'Access-Control-Allow-Credentials'; // 'include', 'same-origin'
const HEADER_ACCESS_CONTROL_ALLOW_METHODS       = 'Access-Control-Allow-Methods';
const HEADER_ACCESS_CONTROL_ALLOW_HEADERS       = 'Access-Control-Allow-Headers';
const HEADER_ACCESS_CONTROL_MAX_AGE             = 'Access-Control-Max-Age'; // Protocol's default is 5
const HEADER_ACCESS_CONTROL_EXPOSE_HEADERS      = 'Access-Control-Expose-Headers';


function isOriginOptionToReturnAnyOrigin( $value ) {
    return $value === false || $value === 'false' || $value === '*' || empty( $value );
}

function isOriginOptionToReflectTheOrigin( $value ) {
    return $value === true || $value === 'true';
}

function isOriginOptionToCheckOrigin( $value ) {
    return is_string( $value ) || is_array( $value );
}

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

    return function ( HttpRequest &$req, HttpResponse &$res, bool &$stop ) use ( &$opt ) {

        // # Origin -----------------------------------------------------------

        $isOriginForbidden = false;

        $origin = $req->header( HEADER_ORIGIN );

        if ( is_null( $origin ) ) { // "Origin" header NOT received

            if ( isOriginOptionToReflectTheOrigin( $opt->origin ) ||
                isOriginOptionToReturnAnyOrigin( $opt->origin )
            ) { // Any origin is allowed
                $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, ANY );
            } else { // list of allowed origins
                $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, NOT_ALLOWED_ORIGIN );
                $res->status( STATUS_FORBIDDEN );
                $isOriginForbidden = true;
            }

        } else {  // "Origin" header received

            if ( isOriginOptionToReflectTheOrigin( $opt->origin ) || isOriginOptionToReturnAnyOrigin( $opt->origin ) ) {

                $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, $origin );
                $res->header( HEADER_VARY, HEADER_ORIGIN ); // Indicates that the Origin value influences the response

            } else if ( isOriginOptionToCheckOrigin( $opt->origin ) ) {

                if ( isOriginAllowed( $origin, $opt->origin ) ) {
                    $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, $origin );
                    $res->header( HEADER_VARY, HEADER_ORIGIN ); // Indicates that the Origin value influences the response
                } else {
                    $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, NOT_ALLOWED_ORIGIN );
                    $res->status( STATUS_FORBIDDEN );
                    $isOriginForbidden = true;
                }

            }

        }

        // # Credentials ------------------------------------------------------

        if ( $opt->credentials ) {
            $res->header( HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS, 'true' );
        }

        // # Allowed Headers --------------------------------------------------

        if ( $opt->allowedHeaders == ANY || empty( $opt->allowedHeaders ) ) {
            $res->header( HEADER_ACCESS_CONTROL_ALLOW_HEADERS, ANY );
        } else {
            $value = is_array( $opt->allowedHeaders )
                ? implode( ',', $opt->allowedHeaders )
                : $opt->allowedHeaders . '';
            $res->header( HEADER_ACCESS_CONTROL_ALLOW_HEADERS, $value );
        }

        // # Methods ----------------------------------------------------------

        if ( $opt->methods === ANY || empty( $opt->methods ) ) {
            $header = $req->header( REQUEST_HEADER__ACCESS_CONTROL_REQUEST_METHOD );
            if ( ! empty( $header ) ) {
                if ( isHttpMethodValid( $header ) ) {
                    $res->header( HEADER_ACCESS_CONTROL_ALLOW_METHODS, mb_strtoupper( $header ) );
                } else {
                    $res->header( HEADER_ACCESS_CONTROL_ALLOW_METHODS, 'GET' ); // Only 'GET' on invalid value
                }
            } else {
                $res->header( HEADER_ACCESS_CONTROL_ALLOW_METHODS, DEFAULT_ALLOWED_METHODS );
            }
        } else {
            $value = is_array( $opt->methods )
                ? implode( ',', $opt->methods )
                : $opt->methods . '';
            $res->header( HEADER_ACCESS_CONTROL_ALLOW_METHODS, $value );
        }

        // # Exposed Headers --------------------------------------------------

        if ( ! empty( $opt->exposedHeaders ) && $opt->exposedHeaders != ANY ) {
            $value = is_array( $opt->exposedHeaders )
                ? implode( ',', $opt->exposedHeaders )
                : $opt->exposedHeaders . '';
            $res->header( HEADER_ACCESS_CONTROL_EXPOSE_HEADERS, $value );
        }

        // # Max Age ----------------------------------------------------------

        if ( is_numeric( $opt->maxAge ) ) {
            $res->header( HEADER_ACCESS_CONTROL_MAX_AGE, $opt->maxAge );
        }

        // --

        if ( $req->method() === METHOD_OPTIONS ) { // Preflight Request

            $res->header( HEADER_CONTENT_LENGTH, 0 );

            if ( ! $isOriginForbidden ) {

                $res->status( STATUS_NO_CONTENT );

                // # Options' success status --------------------------------------
                if ( ! empty( $opt->optionsSuccessStatus ) && $opt->optionsSuccessStatus != 204 ) {
                    $res->status( $opt->optionsSuccessStatus );
                }
            }

            // # Preflight Continue -------------------------------------------
            if ( $opt->preflightContinue ) {
                return;
            }

            $stop = true;
            $res->end();
            return;
        }

    };
}

?>
