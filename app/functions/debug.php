<?php

if ( getenv('APP_DEBUG') == 'true' )
{
    error_reporting( E_ALL );
    ini_set( 'display_errors', 'on' );
}
else
{
    error_reporting( 0 );
    ini_set( 'display_errors', 'off' );
}

