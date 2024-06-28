<?php
namespace phputil\cors;

use function strtoupper;
use function array_search;
use function is_array;

// HTTP STATUS CODES ----------------------------------------------------------

const STATUS_NO_CONTENT = 204;
const STATUS_FORBIDDEN = 403;

// HTTP METHODS ---------------------------------------------------------------

const METHOD_GET        = 'GET';
const METHOD_POST       = 'POST';
const METHOD_PUT        = 'PUT';
const METHOD_DELETE     = 'DELETE';
const METHOD_OPTIONS    = 'OPTIONS';
const METHOD_HEAD       = 'HEAD';
const METHOD_PATCH      = 'PATCH';

const SUPPORTED_METHODS = [
    METHOD_GET,
    METHOD_POST,
    METHOD_PUT,
    METHOD_DELETE,
    METHOD_OPTIONS,
    METHOD_HEAD,
    METHOD_PATCH
];

// UTILITIES ------------------------------------------------------------------

function isHttpMethodValid( $method ) {
    return array_search( strtoupper( $method ), SUPPORTED_METHODS ) !== false;
}

function isOriginAllowed( $requestOrigin, $originToCheck ) {
    if ( is_string( $originToCheck ) ) {
        return $requestOrigin === $originToCheck;
    }
    return is_array( $originToCheck ) && in_array( $requestOrigin, $originToCheck, true );
}

?>