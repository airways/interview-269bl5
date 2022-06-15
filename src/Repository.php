<?php

namespace Collegeplannerpro\InterviewReport;

use Collegeplannerpro\InterviewReport\Domain\Invoice;
use Collegeplannerpro\InterviewReport\Domain\InvoicePayment;
use Collegeplannerpro\InterviewReport\Domain\Contact;

class Repository
{
    public function __construct(private \mysqli $db) {}

    public function allInvoices(): \mysqli_result
    {
        return $this->db->query(<<<SQL
            SELECT i.*, c.first_name, c.last_name,
            (SELECT SUM(amount) FROM payments p WHERE i.invoice_id = p.invoice_id) AS amount_paid,
            total - (SELECT SUM(amount) FROM payments p WHERE i.invoice_id = p.invoice_id) AS balance
            FROM invoices i
            NATURAL JOIN contacts c
            ORDER BY i.issued_at
SQL
        );
    }

    public function allInvoicesWithPayments(): array // of Invoice
    {
        $invoiceAndPaymentResult = $this->db->query(<<<SQL
            SELECT i.*, c.first_name, c.last_name,
            (SELECT SUM(amount) FROM payments p WHERE i.invoice_id = p.invoice_id) AS amount_paid,
            total - (SELECT SUM(amount) FROM payments p WHERE i.invoice_id = p.invoice_id) 
                AS balance,
            p.payment_id, p.amount, p.paid_at
            FROM invoices i
            NATURAL JOIN contacts c
            LEFT JOIN payments p ON p.invoice_id = i.invoice_id
            ORDER BY i.issued_at, p.payment_id
LIMIT 500 -- TODO: REMOVE ME -- for css/js testing
SQL
        );

        $invoices = [];
        $lastInvoiceId = null;
        $invoice = null;

        while ($row = $invoiceAndPaymentResult->fetch_assoc()) {
            if($row['invoice_id'] != $lastInvoiceId) {
                $lastInvoiceId = $row['invoice_id'];
                // Not directly creating Invoice so we can use the same cache as invoiceDetails
                $invoice = $this->invoiceDetails($lastInvoiceId, $row);
                $invoices[] = $invoice;
            }
            if(!is_null($row['amount'])) {
                $invoice->payments[] = new InvoicePayment($row);
            }
        }

        return $invoices;
    }

    public function invoicePayments($invoiceId): \mysqli_result
    {
        return $this->db->query(
            "SELECT * FROM payments WHERE invoice_id = ? ORDER BY paid_at ASC",
            [$invoiceId]
        );
    }
    
    public function contactDetails(int $contactId): Contact
    {
        // Results are cached so that we do not get different Contact objects for the same ID
        // within the same request
        static $contacts = [];
        if(isset($contacts[$contactId])) { return $contacts[$contactId]; }
        
        $result = new Contact($this->queryWithParams(
            "SELECT * FROM contacts WHERE contact_id = ? LIMIT 1",
            [$contactId]
        ));
        
        $contacts[$contactId] = $result;
        return $result;
    }

    public function invoiceDetails(int $invoiceId, $fromDatabaseRow = null): Invoice
    {
        static $invoices = [];
        if(isset($invoices[$invoiceId])) { return $invoices[$invoiceId]; }
        
        if(is_null($fromDatabaseRow)) {
            $fromDatabaseRow = $this->queryWithParams(
                "SELECT * FROM contacts WHERE contact_id = ? LIMIT 1",
                [$contactId]
            );
        }

        $result = new Invoice($fromDatabaseRow);
        
        $invoices[$invoiceId] = $result;
        return $result;
    }

    private function queryWithParams(string $sql, array $params): array
    {
        $stmt = $this->db->prepare($sql); 
        if(count($params) > 0) {
            $refValues = $this->refValues($params);
            call_user_func_array(array($stmt, 'bind_param'), $refValues);
        } else {
            $res = @mysqli_query($hesk_db_link, $query);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * This functions generates reference values from an array so it can be passed to mysqli bind_param
     * which otherwise does not accept plain arrays for parameters.
     */
    private function refValues(&$arr): array
    {
        if (strnatcmp(phpversion(),'5.3') >= 0) //Reference is required for PHP 5.3+
        {
            $refs = array(0);
            $types = '';
            foreach($arr as $key => $value) {
                if(is_numeric($value)) {
                    $typ = 'i';
                    $value = (int)$value;
                } else {
                    $typ = 's';
                }
                $types .= $typ;
                $refs[$key+1] = &$arr[$key];
            }
            $refs[0] = $types;
            return $refs;
        }
        return $arr;
    }

}
