#!/usr/bin/php -q
<?php

require __DIR__ . '/../vendor/autoload.php';

/** @var mysqli $db */
$db = require __DIR__ . '/../dbConnection.php';
require __DIR__ . '/../inc/insertStatements.inc.php';

$faker = \Faker\Factory::create();

$db->query('TRUNCATE payments');
$db->query('TRUNCATE invoices');
$db->query('TRUNCATE contacts');

$thisYear = date('Y');
$lastYear = $thisYear - 1;

echo "\nLoading fake invoice data...";

for ($i = 0; $i < 2000; $i++) {
    $contactStmt->execute([
        $faker->firstName(),
        $faker->lastName(),
        $faker->email(),
        $faker->streetAddress(),
        $faker->city(),
        $faker->stateAbbr(),
        $faker->postcode(),
        $faker->phoneNumber(),
    ]);
    $contactId = $contactStmt->insert_id;

    $invoiceCount = $faker->numberBetween(1, 12);

    // pre-sort issue dates so invoice identifiers will correspond
    $issueDates = [];
    for ($j = 0; $j < $invoiceCount; $j++) {
        $issueDates[] = $faker->dateTimeBetween("$lastYear-01-01", "$thisYear-01-01");
    }
    sort($issueDates);

    for ($j = 0; $j < $invoiceCount; $j++) {
        $identifier = sprintf('%06d-%04d', $contactId, $j+1);
        $total = $faker->numberBetween(6, 200) * 2500;  // $125-5000 in increments of $25
        $issuedAt = $issueDates[$j];
        $invoiceStmt->execute([
            $contactId,
            $identifier,
            $total,
            $issuedAt->getTimestamp(),
        ]);
        $invoiceId = $invoiceStmt->insert_id;

        // 70% of invoices have been paid in full
        $amountIn25ToHavePaid = $faker->optional(0.3, $total / 2500)
            ->numberBetween(0, $total / 2500);

        while ($amountIn25ToHavePaid > 0) {
            $paymentAmount = $faker->numberBetween(1, $amountIn25ToHavePaid) * 2500;
            $paidAt = $faker->dateTimeBetween($issuedAt, "$thisYear-01-01");
            $paymentStmt->execute([$invoiceId, $paymentAmount, $paidAt->getTimestamp()]);
            $amountIn25ToHavePaid -= $paymentAmount / 2500;
        }
    }
    echo '.';
}

echo "\n";
