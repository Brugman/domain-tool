<?php

$required_files = [
    '/vendor/autoload.php', // ran composer
    '/.env', // created env config
    '/public_html/assets/css/app.min.css', // ran gulp build
];

foreach ( $required_files as $required_file )
    if ( !is_file( dirname( getcwd() ).$required_file ) )
        exit('There are missing files. Please follow the README installation.');

