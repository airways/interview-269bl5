<?php
declare(strict_types=1);

require 'ControllerTestCase.php';

use Fig\Http\Message\StatusCodeInterface;
use Carbon\Carbon;

/**
 * ReportControllerIntegrationTest
 * 
 * Warning: Should only be run against a test database, as it will
 * tuncate the invoices, payments and contacts tables.
 * 
 */
final class ReportControllerIntegrationTest extends ControllerTestCase
{
    public function setUp(): void
    {
        $this->assertTrue((bool)getenv('IS_TEST_CONTAINER', FALSE));

        $this->app = $this->getAppInstance();
        $db = $this->app->getContainer()->get('db');
        include __DIR__ . '/../inc/insertStatements.inc.php';

        $db->query('TRUNCATE payments');
        $db->query('TRUNCATE invoices');
        $db->query('TRUNCATE contacts');

        $contactStmt->execute([
            'Morgana',
            'Stehra',
            'mstehr@example.com',
            '116 McKenzie Villages',
            'Lake Lloyd',
            'CA',
            '61622',
            '+17549651658',
        ]);
        $contactId = $contactStmt->insert_id;
    
        $invoiceCount = 2;
        $totals = [452500, 367500];
        
        $issueDates = ['2021-01-01 00:37:58', '2021-02-05 21:49:03'];
        sort($issueDates);

        $payments = [
            [
                '2021-04-18 16:27:16' =>	2500,
                '2021-06-07 03:08:47' =>	60000,
                '2021-07-03 13:54:24' =>	2500,
                '2021-09-25 01:40:34' =>	170000,
                '2021-10-14 12:30:18' =>	12500,
                '2021-12-11 23:58:57' =>	2500,
                '2021-12-30 03:00:05' =>	30000,
            ],
            [
                '2021-07-17 01:05:51' =>	7500,
                '2021-08-26 07:51:30' =>	2500,
                '2021-09-17 05:40:25' =>	357500,
            ],
        ];

        for ($j = 0; $j < $invoiceCount; $j++) {
            $identifier = sprintf('%06d-%04d', $contactId, $j+1);
            $total = $totals[$j];
            $issuedAt = new Carbon($issueDates[$j]);
            $invoiceStmt->execute([
                $contactId,
                $identifier,
                $total,
                $issuedAt->getTimestamp(),
            ]);
            $invoiceId = $invoiceStmt->insert_id;
    
            foreach($payments[$j] as $paidAt => $paymentAmount) {
                $paidAt = new Carbon($paidAt);
                $paymentStmt->execute([$invoiceId, $paymentAmount, $paidAt->getTimestamp()]);
            }
        }
    }

    public function testInvoicesListed(): void
    {
        // Create slim request and response objects for action to use
        $request = $this->createRequest('GET', '/reports/payments', 'format=json');
        $response = new \Slim\Psr7\Response();

        // Execute report action
        $response = $this->getAppInstance()->handle($request);
        
        $data = json_decode((string)$response->getBody());

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
        $this->assertObjectHasAttribute('invoices', $data);
        $this->assertSame(count($data->invoices), 2);
        
        $this->assertSame('Morgana', $data->invoices[0]->first_name);
        $this->assertSame('Stehra', $data->invoices[0]->last_name);
        $this->assertSame((new Carbon('2021-01-01 00:37:58'))->toIso8601String(),
                          (new Carbon($data->invoices[0]->issued_at))->toIso8601String());
        $this->assertSame(452500, (int)$data->invoices[0]->total);
        $this->assertSame(280000, (int)$data->invoices[0]->amount_paid);
        $this->assertSame(172500, (int)$data->invoices[0]->balance);
        $this->assertSame(7, count($data->invoices[0]->payments));
        $this->assertSame((int)$data->invoices[0]->invoice_id, $data->invoices[0]->payments[0]->invoice_id);
        $this->assertSame(2500, (int)$data->invoices[0]->payments[0]->amount);
        $this->assertSame((new Carbon('2021-04-18 16:27:16'))->toIso8601String(),
                          (new Carbon($data->invoices[0]->payments[0]->paid_at))->toIso8601String());
        
        $this->assertSame((int)$data->invoices[0]->contact_id, (int)$data->invoices[1]->contact_id);
        $this->assertSame('Morgana', $data->invoices[1]->first_name);
        $this->assertSame('Stehra', $data->invoices[1]->last_name);
        $this->assertSame((new Carbon('2021-02-05 21:49:03'))->toIso8601String(),
                          (new Carbon($data->invoices[1]->issued_at))->toIso8601String());
        $this->assertSame(367500, (int)$data->invoices[1]->total);
        $this->assertSame(367500, (int)$data->invoices[1]->amount_paid);
        $this->assertSame(0, (int)$data->invoices[1]->balance);
        $this->assertSame(3, count($data->invoices[1]->payments));
        $this->assertSame((int)$data->invoices[1]->invoice_id, (int)$data->invoices[1]->payments[0]->invoice_id);
        $this->assertSame(7500, (int)$data->invoices[1]->payments[0]->amount);
        $this->assertSame((new Carbon('2021-07-17 01:05:51'))->toIso8601String(),
                          (new Carbon($data->invoices[1]->payments[0]->paid_at))->toIso8601String());
    }
}
