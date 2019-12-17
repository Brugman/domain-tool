<?php

if ( getenv('APP_DEBUG') )
{
    error_reporting( E_ALL );
    ini_set( 'display_errors', 'on' );
}

