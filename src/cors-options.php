<?php
namespace phputil\cors;

/**
 * CORS options
 */
class CorsOptions {

    public $origin = '*';
    public $methods = 'GET,HEAD,PUT,PATCH,POST,DELETE';
    public $preflightContinue = false;
    public $optionsSuccessStatus = 204; // No Content
    public $credentials = false;
    public $allowedHeaders = [];
    public $exposedHeaders = [];
    public $maxAge = null;

    /**
     * Reads options from an array with the same keys.
     *
     * @param array $options Options
     * @param bool $validate Validate or not (the default value is true)
     * @return CorsOptions
     */
    public function fromArray( array $options, $validate = true ) {
        $attributes = \get_object_vars( $this );
        foreach ( $options as $key => $value ) {
            if ( isset( $attributes[ $key ] ) ) {
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

}

?>