<?php

if ( isset( $_ENV['APP_DEBUG'] ) && $_ENV['APP_DEBUG'] == 'true' )
{
    error_reporting( E_ALL );
    ini_set( 'display_errors', 'on' );
}
elseif ( isset( $_ENV['APP_DEBUG'] ) && $_ENV['APP_DEBUG'] == 'false' )
{
    error_reporting( 0 );
    ini_set( 'display_errors', 'off' );
}

