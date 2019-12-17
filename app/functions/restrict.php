<?php

$access_allowed = false;

if ( getenv('APP_ENV') == 'local' )
    $access_allowed = true;

if ( isset( $_GET['password'] ) && $_GET['password'] == access_password() )
    $access_allowed = true;

if ( !$access_allowed )
    exit( 'Access restricted.' );

