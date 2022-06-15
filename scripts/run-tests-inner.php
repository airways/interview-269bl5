#!/usr/local/bin/php -q
<?php // Note: Do not call this scripot directly! Instead, call run-tests.php outside a container.

if(!getenv('IS_TEST_CONTAINER')) {
    echo 'Do not run this script directly!';
    echo 'Instead, call run-tests.php outside a container.';
    exit(2);
}


// Separate migration log is used to track tests project's migrations
// PHPMIG_LOG is used in phpmig.php config to know what file to use.
passthru('PHPMIG_LOG=.tests-migrations.log /app/vendor/bin/phpmig migrate');

passthru('/app/vendor/bin/phpunit --verbose tests', $exitCode);

unlink('/app/migrations/.tests-migrations.log');

exit($exitCode);
