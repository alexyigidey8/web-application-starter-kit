<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

umask(0000);

/********** Set Timezone **********/
date_default_timezone_set('Europe/Vienna');

/********** Definitions **********/
require __DIR__.'/core/definitions.php';

/********** Autoloader **********/
$vendorAutoloaderFilePath = VENDOR_DIR.'/autoload.php';
if (!file_exists($vendorAutoloaderFilePath) && php_sapi_name() != 'cli') {
    throw new \Exception('Please run "composer install" first!');
}

$autoloader = include $vendorAutoloaderFilePath;

/********** Application **********/
$app = new Application();

require APP_DIR.'/core/providers.php';
require APP_DIR.'/core/middlewares.php';
require APP_DIR.'/core/routes.php';

Request::enableHttpMethodParameterOverride();

return $app;
