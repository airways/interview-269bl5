#!/usr/local/bin/php -q
<?php // Note: This script is meant to be executed *outside* of the container
require_once __DIR__.'/../inc/ansiColor.inc.php';
use PhpAnsiColor\Color;

if(file_exists('/app')) {
    fwrite(STDERR, Color::set('Please run this script outside of any docker container,'.PHP_EOL.
                              'it starts a new copy of the project in order to execute '.PHP_EOL.
                              'tests in a fully isolated environment.'.PHP_EOL.PHP_EOL, 'red'));
    exit(1);
}

$testStack = '.test-docker-compose.yml';
if(file_exists($testStack)) {
    unlink($testStack);
}

// Prevent binding conflicts by changing the ports used for the `tests` project
$testDockerComposeConfig = file_get_contents('docker-compose.yml');
$testDockerComposeConfig = str_replace('13306', '13307', $testDockerComposeConfig);
$testDockerComposeConfig = str_replace('8080', '36789', $testDockerComposeConfig);
file_put_contents('.test-docker-compose.yml', $testDockerComposeConfig);
if(!file_exists($testStack)) {
    fwrite(STDERR, 'Unable to create '.$testStack.' config file to start test project!'); 
}

$exitCode = 0;
passthru('
docker-compose \
    -p tests \
    -f .test-docker-compose.yml run \
    web \
    bash -c "IS_TEST_CONTAINER=1 /app/scripts/run-tests-inner.php"
', $exitCode);

echo PHP_EOL;
echo 'Cleaning up...'.PHP_EOL;

passthru('
docker-compose \
    -p tests down
');

passthru('
docker-compose \
    -p tests rm
');

unlink($testStack);

echo PHP_EOL;
if($exitCode) {
    fwrite(STDERR, 'Tests '.Color::set('FAILED', 'red+underline').' [code: '.$exitCode.']');
} else {
    echo 'Tests '.Color::set("PASSED", 'green+bold');
}
