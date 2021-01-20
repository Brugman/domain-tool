<?php

// installaton check
include 'functions/installaton-check.php';

// composer
include dirname( getcwd() ).'/vendor/autoload.php';

// dotenv
$dotenv = Dotenv\Dotenv::createImmutable( dirname( getcwd() ) );
$dotenv->load();

include 'functions/debug.php';
include 'functions/functions.php';

include 'components/core-head.php';

if ( access_granted() )
{
    include 'components/header.php';
    include 'components/app.php';
    include 'components/footer.php';
}
else
{
    include 'components/header.php';
    include 'components/auth.php';
}

include 'components/core-foot.php';

