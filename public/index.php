<?php

$time = time();

define('STDOUT', fopen('log.'.$time.'.txt', 'w'));

if (function_exists('memprof_enable')) {
  memprof_enable();

  register_shutdown_function(function() {
    memprof_dump_callgrind(fopen(__DIR__ . "/../memprof.$time.out", "w"));
  });
}

function debug($one, $two = null) {
  if (isset($two)) {
    $label = (string) $one;
    $value = $two;
    $output = $label . ': ' . (is_string($value) ? $value : var_export($value, true));
  } else {
    $value = $one;
    $output = (is_string($value) ? $value : var_export($value, true));
  }
  fwrite(STDOUT, $output . "\n");
}

function memory_get_usage_mb() {
  return number_format(memory_get_usage() / (1024 * 1024), 2);
}

function debug_hr() {
  fwrite(STDOUT, "---------------------------------------------\n");
}

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

require_once __DIR__ . '/../vendor/autoload.php';

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap()
            ->run();

