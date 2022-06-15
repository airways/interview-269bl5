<?php

use \Phpmig\Adapter;

$phpmigContainer = new ArrayObject();

// TODO: Replace with database adapter to store migration data in the DB itself.
// For sake of time, leaving the plain text log instead (and adding it to .gitignore)
$logFilename = getenv('PHPMIG_LOG') ?: '.migrations.log';
$phpmigContainer['phpmig.adapter'] = new Adapter\File\Flat(__DIR__.DIRECTORY_SEPARATOR.'migrations/'.$logFilename);

$phpmigContainer['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

include "bootstrap.php";

$phpmigContainer['db'] = $db;

return $phpmigContainer;
