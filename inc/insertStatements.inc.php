<?php

/**
 * This include file is used by the seed.php script and by integration tests to insert
 * test data.
 * 
 * TODO: Refactor to true DAL.
 * 
 */
$contactSql = 'INSERT INTO contacts (first_name, last_name, email, street_address, city, state_code, postal_code, phone, country_code)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, "US")';
$contactStmt = $db->prepare($contactSql);
$invoiceSql = 'INSERT INTO invoices (contact_id, identifier, total, issued_at) VALUES (?, ?, ?, FROM_UNIXTIME(?))';
$invoiceStmt = $db->prepare($invoiceSql);
$paymentSql = 'INSERT INTO payments (invoice_id, amount, paid_at) VALUES (?, ?, FROM_UNIXTIME(?))';
$paymentStmt = $db->prepare($paymentSql);
