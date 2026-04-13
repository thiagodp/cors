<?php
require_once __DIR__ . '/../shared.php';

$port = '9998';

return [
    'domain'    => "$host:$port",
    'localhost' => "http://$host:$port",
    'allowed'   => 'http://allowed.com',
];