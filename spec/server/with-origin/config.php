<?php
$port = '9998';
$host = PHP_OS_FAMILY === 'Windows' ? 'localhost' : '0.0.0.0';

return [
    'domain'    => "$host:$port",
    'localhost' => "http://$host:$port",
    'allowed'   => 'http://allowed.com',
];