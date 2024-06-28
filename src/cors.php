<?php
namespace phputil\cors;

use DateTime;
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

const INVALID_ORIGIN_VALUE = 'false';

function isOriginOptionToReturnAnyOrigin( $value ) {
    return $value === null || $value === false || $value === 'false' || $value === '*' || $value === '';
}

function isOriginOptionToReflectTheOrigin( $value ) {
    return $value === true || $value === 'true';
}

function isOriginOptionASingleOrigin( $value ) {
    return is_string( $value );
}

/**
 * CORS middleware.
 *
 * @param array|CorsOptions $options CORS options.
 * @return callable
 */
function cors( $options = [] ) {

    // if ( isset( $options[ 'log' ] ) ) {
    //     @file_put_contents( $options[ 'log' ], date_format( new DateTime(), 'd M Y H:i:s' ) . ' - ' . json_encode( $options ) . "\n", FILE_APPEND );
    // }

    $opt = is_array( $options )
        ? ( new CorsOptions() )->fromArray( $options )
        : ( ( $options instanceof CorsOptions ) ? $options : new CorsOptions() );

    return function ( HttpRequest &$req, HttpResponse &$res, bool &$stop ) use ( &$opt ) {

        $requestMethod = $req->method();

        if ( $requestMethod === METHOD_OPTIONS ) {

            $res->status( STATUS_NO_CONTENT );
            $res->header( HEADER_CONTENT_LENGTH, 0 );

            // # Options' success status --------------------------------------
            if ( ! empty( $opt->optionsSuccessStatus ) && $opt->optionsSuccessStatus != 204 ) {
                $res->status( $opt->optionsSuccessStatus );
            }
        }

        // # Origin -----------------------------------------------------------

        $requestOrigin = $req->header( HEADER_ORIGIN );

        if ( isOriginOptionToReturnAnyOrigin( $opt->origin ) ) {

            $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, ANY );

        } else if ( isOriginOptionASingleOrigin( $opt->origin ) ) {

            $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, $opt->origin );
            $res->header( HEADER_VARY, HEADER_ORIGIN );

        } else if ( isOriginOptionToReflectTheOrigin( $opt->origin ) ) {

            if ( isset( $requestOrigin ) ) {
                $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, $requestOrigin );
                $res->header( HEADER_VARY, HEADER_ORIGIN );
            } else {
                $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, ANY ); // Permissive
            }

        } else { // List of origins

                if ( isOriginAllowed( $requestOrigin, $opt->origin ) ) {

                    // Reflect origin
                    $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, $requestOrigin );
                    $res->header( HEADER_VARY, HEADER_ORIGIN );

                } else {

                    $firstOrigin = is_array( $opt->origin ) ? ( $opt->origin[ 0 ] ?? INVALID_ORIGIN_VALUE ) : $opt->origin;
                    $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, $firstOrigin );

                    if ( $requestMethod === METHOD_OPTIONS ) {
                        $res->status( STATUS_FORBIDDEN );
                    }
                }
        }

        // # Methods ----------------------------------------------------------

        if ( $opt->methods === ANY || empty( $opt->methods ) ) {

            $preflightRequestedMethod = $req->header( REQUEST_HEADER__ACCESS_CONTROL_REQUEST_METHOD );

            if ( $requestMethod === METHOD_OPTIONS ) {

                if ( ! is_null( $preflightRequestedMethod ) ) {
                    if ( isHttpMethodValid( $preflightRequestedMethod ) ) {
                        $res->header( HEADER_ACCESS_CONTROL_ALLOW_METHODS, strtoupper( $preflightRequestedMethod ) );
                    } else {
                        $res->header( HEADER_ACCESS_CONTROL_ALLOW_METHODS, METHOD_GET ); // Only 'GET' on invalid value
                    }
                } else {
                    $res->header( HEADER_ACCESS_CONTROL_ALLOW_METHODS, DEFAULT_ALLOWED_METHODS );
                }
            }

        } else {
            $value = is_array( $opt->methods )
                ? implode( ',', $opt->methods )
                : $opt->methods . '';
            $res->header( HEADER_ACCESS_CONTROL_ALLOW_METHODS, $value );
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

        if ( $requestMethod === METHOD_OPTIONS ) { // Preflight Request

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
