<?php defined('SYSPATH') OR die('No direct access allowed.');

return array
(
    'wsdldirectory' => MODPATH.'kohana-shipping'.DIRECTORY_SEPARATOR.'wsdl'.DIRECTORY_SEPARATOR.'ups'.DIRECTORY_SEPARATOR,
    'currency' => array(
        'USD' => array
        (
            'Username' => NULL,
            'Password' => NULL,

            'AccessLicenseNumber' => NULL,

            'location' => 'https://wwwcie.ups.com',
        ),
        'CAD' => array
        (
            'Username' => NULL,
            'Password' => NULL,

            'AccessLicenseNumber' => NULL,

            'location' => 'https://wwwcie.ups.com',
        ),
    ),
    'service' => array(
        'Ship' => array(
            'wsdl' => 'Ship.wsdl',
            'serviceId' => 'aval',
            'major' => '2',
            'intermediate' => '0',
            'minor' => '0',
            'location' => '/webservices/Ship',
        )
    ),
);
