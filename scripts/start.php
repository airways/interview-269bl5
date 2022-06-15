#!/usr/bin/php -q
<?php

chdir('/app');
unlink('/app/migrations/.migrations.log');
passthru('/app/vendor/bin/phpmig migrate');
passthru('php -S 0.0.0.0:80 -t /app/public');
