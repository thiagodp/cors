<?php // kahlan-config.php

use Kahlan\Filter\Filters;

Filters::apply($this, 'patchers', function($next) {

    $target = '/vendor/phputil/cors/cors.php';

    $files = $this->autoloader()->files();
    foreach ($files as $key => $file) {
        if ($file === __DIR__ . $target ) {
            unset($files[$key]);
        }
    }
    $this->autoloader()->files($files);

    // \Kahlan\Jit\includeFile(__DIR__ . $target);

    return $next();

});