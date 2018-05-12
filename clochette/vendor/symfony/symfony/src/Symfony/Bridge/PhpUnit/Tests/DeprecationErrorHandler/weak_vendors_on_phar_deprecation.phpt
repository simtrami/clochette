--TEST--
Test DeprecationErrorHandler in weak vendors mode on eval()'d deprecation
The phar can be regenerated by running php src/Symfony/Bridge/PhpUnit/Tests/DeprecationErrorHandler/generate_phar.php
--FILE--
<?php

putenv('SYMFONY_DEPRECATIONS_HELPER=weak_vendors');
putenv('ANSICON');
putenv('ConEmuANSI');
putenv('TERM');

$vendor = __DIR__;
while (!file_exists($vendor.'/vendor')) {
    $vendor = dirname($vendor);
}
define('PHPUNIT_COMPOSER_INSTALL', $vendor.'/vendor/autoload.php');
require PHPUNIT_COMPOSER_INSTALL;
require_once __DIR__.'/../../bootstrap.php';
\Phar::loadPhar(__DIR__.'/deprecation.phar', 'deprecation.phar');
include 'phar://deprecation.phar/deprecation.php';

?>
--EXPECTF--

Other deprecation notices (1)

  1x: I come from… afar! :D
