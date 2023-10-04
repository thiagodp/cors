<?php
namespace phputil\cors;

use RuntimeException;
use function array_key_exists;
use function explode;
use function get_object_vars;
use function is_array;
use function is_numeric;
use function is_string;

const ANY = '*';
const DEFAULT_ALLOWED_METHODS = 'GET,HEAD,OPTIONS,POST,PUT,DELETE,PATCH';

/**
 * CORS options.
 *
 * The options properties are compatible with Troy Goode's ExpressJS Cors middleware,
 * available at https://github.com/expressjs/cors with a MIT license.
 */
class CorsOptions {

    /** @var bool|string|array Allowed origins. Used to make `Access-Control-Allow-Origin` */
    public $origin = true;

    /** @var string|array Equivalent to `Access-Control-Allow-Methods` */
    public $methods = DEFAULT_ALLOWED_METHODS;

    /** @var bool Equivalent to `Access-Control-Allow-Credentials` */
    public $credentials = true;

    /** @var string|array Equivalent to `Access-Control-Allow-Headers` */
    public $allowedHeaders = ANY;

    /** @var string|array Equivalent to `Access-Control-Expose-Headers` */
    public $exposedHeaders = ''; // None

    /** @var int|null Equivalent to `Access-Control-Max-Age` */
    public $maxAge = null;

    /** @var bool If the middleware should allow to continue the response after the preflight. */
    public $preflightContinue = false;

    /** @var int Successful status when OPTIONS is sent. */
    public $optionsSuccessStatus = 204; // No Content


    /**
     * Reads options from an array with the same keys.
     *
     * @param array $options Options
     * @param bool $validate Validate or not (the default value is true)
     * @return CorsOptions
     */
    public function fromArray( array $options, $validate = true ) {
        $attributes = get_object_vars( $this );
        foreach ( $options as $key => $value ) {
            if ( array_key_exists( $key, $attributes ) ) {
                $this->{ $key } = $value;
            }
        }
        if ( $validate ) {
            $this->validate();
        }
        return $this;
    }

    /**
     * Validates the options and throws an exception in case of a problem.
     *
     * @throws \RuntimeException
     */
    public function validate() {
        validateOptions( $this );
    }

    //
    // Build methods
    //

    /**
     * Sets the origin.
     *
     * @param string|array $value
     * @return CorsOptions
     */
    public function withOrigin( $value ) { $this->origin = $value; return $this; }

    /**
     * Sets the methods.
     *
     * @param string|array $value
     * @return CorsOptions
     */
    public function withMethods( $value ) { $this->methods = $value; return $this; }

    /**
     * Sets the credentials.
     *
     * @param bool $value
     * @return CorsOptions
     */
    public function withCredentials( $value ) { $this->credentials = $value; return $this; }

    /**
     * Sets the allowedHeaders.
     *
     * @param string|array $value
     * @return CorsOptions
     */
    public function withAllowedHeaders( $value ) { $this->allowedHeaders = $value; return $this; }

    /**
     * Sets the exposedHeaders.
     *
     * @param string|array $value
     * @return CorsOptions
     */
    public function withExposedHeaders( $value ) { $this->exposedHeaders = $value; return $this; }

    /**
     * Sets the maxAge.
     *
     * @param int $value
     * @return CorsOptions
     */
    public function withMaxAge( $value ) { $this->maxAge = $value; return $this; }

    /**
     * Sets the preflightContinue.
     *
     * @param bool $value
     * @return CorsOptions
     */
    public function withPreflightContinue( $value ) { $this->preflightContinue = $value; return $this; }

    /**
     * Sets the optionsSuccessStatus.
     *
     * @param int $value
     * @return CorsOptions
     */
    public function withOptionsSuccessStatus( $value ) { $this->optionsSuccessStatus = $value; return $this; }
}

//
// Validation
//

const MSG_INVALID_METHODS_TYPE = 'The option "methods" must be a string or an array.';
const MSG_INVALID_HTTP_METHOD = 'Invalid HTTP method.';
const MSG_INVALID_SUCCESS_STATUS = 'Invalid success status code. It should be 200 or 204.';

function validateOptions( CorsOptions $co ) {
    // Methods
    $methodsToValidate = [];
    if ( is_string( $co->methods ) ) {
        $methodsToValidate = explode( ',', $co->methods );
    } else if ( is_array( $co->methods ) ) {
        $methodsToValidate = $co->methods;
    } else {
        throw new RuntimeException( MSG_INVALID_METHODS_TYPE );
    }
    // HTTP methods
    foreach ( $methodsToValidate as $m ) {
        if ( ! isHttpMethodValid( trim( $m ) ) ) {
            throw new RuntimeException( MSG_INVALID_HTTP_METHOD );
        }
    }
    // Status
    if ( ! is_numeric( $co->optionsSuccessStatus ) ||
        ! ( $co->optionsSuccessStatus == 200 || $co->optionsSuccessStatus == 204 )
    ) {
        throw new RuntimeException( MSG_INVALID_SUCCESS_STATUS );
    }
}

?>