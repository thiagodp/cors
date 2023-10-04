<?php
namespace phputil\cors;

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

const NOT_ALLOWED_ORIGIN = 'false';

// CORS Request Headers
const HEADER_ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';

// CORS Response Headers
const HEADER_ACCESS_CONTROL_ALLOW_ORIGIN        = 'Access-Control-Allow-Origin';      // URL, 'null' or '*'
const HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS   = 'Access-Control-Allow-Credentials'; // 'include', 'same-origin'
const HEADER_ACCESS_CONTROL_ALLOW_METHODS       = 'Access-Control-Allow-Methods';
const HEADER_ACCESS_CONTROL_ALLOW_HEADERS       = 'Access-Control-Allow-Headers';
const HEADER_ACCESS_CONTROL_MAX_AGE             = 'Access-Control-Max-Age'; // Protocol's default is 5
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

        // # Origin -----------------------------------------------------------

        $origin = $req->header( HEADER_ORIGIN );
        if ( is_null( $origin ) || $opt->origin === false ) {
            $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, ANY );

        } else {

            $canIncludeRequestOrigin = $opt->origin === true ||
                $opt->origin === ANY ||
                (
                    ( is_array( $opt->origin ) || is_string( $opt->origin ) )
                    && isOriginAllowed( $origin, $opt->origin )
                );

            // die( ( $canIncludeRequestOrigin ? 'S' : 'N' ) . ' O:' . $origin );

            if ( $canIncludeRequestOrigin ) {
                // Sets the Origin as allowed
                $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, $origin );

                // Indicates that the Origin value influences the response
                $res->header( HEADER_VARY, HEADER_ORIGIN );
            } else {
                $res->header( HEADER_ACCESS_CONTROL_ALLOW_ORIGIN, NOT_ALLOWED_ORIGIN );
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
            $res->header( HEADER_ACCESS_CONTROL_ALLOW_METHODS, DEFAULT_ALLOWED_METHODS );
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

            // # Preflight Continue -------------------------------------------
            if ( $opt->preflightContinue ) {
                return;
            }

            $stop = true;

            // # Options' success status --------------------------------------
            $res->status( $opt->optionsSuccessStatus )->header( HEADER_CONTENT_LENGTH, 0 )->end();
            return;
        }

    };
}

?>
