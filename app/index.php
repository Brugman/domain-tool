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

restrict_access();

$title = 'Domain Tool';

include 'components/core-head.php';

include 'components/header.php';
include 'components/app.php';
include 'components/footer.php';

include 'components/core-foot.php';

