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
        
        $stmt = $this->db->prepare("SELECT * FROM contacts WHERE contact_id = ? LIMIT 1");
        if($stmt->execute([$contactId])) {
            $res = $stmt->get_result();
            $row = $res->fetch_assoc();
            $result = new Contact($row);
            $contacts[$contactId] = $result;
            return $result;
        } else {
            error_log('!! Could not find Contact with id '.$contactId);
            return null;
        }
    }

    public function invoiceDetails(int $invoiceId, $fromDatabaseRow = null): Invoice
    {
        static $invoices = [];
        if(isset($invoices[$invoiceId])) { return $invoices[$invoiceId]; }
        
        if(is_null($fromDatabaseRow)) {
            $stmt = $this->db->prepare("SELECT *, c.first_name, c.last_name FROM invoices NATURAL JOIN contacts c WHERE invoice_id = ? LIMIT 1");
            if($stmt->execute([$invoiceId])) {
                $res = $stmt->get_result();
                $row = $res->fetch_assoc();
                $fromDatabaseRow = $row;
            } else {
                error_log('!! Could not find Invoice with id '.$invoiceId);
                return null;
            }
        }

        $result = new Invoice($fromDatabaseRow);
        
        $invoices[$invoiceId] = $result;
        return $result;
    }

}
