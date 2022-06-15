<?php

namespace Collegeplannerpro\InterviewReport\Domain;

use Carbon\Carbon;

class Invoice
{
    public int $invoice_id;

    public int $contact_id;

    public string $first_name;

    public string $last_name;

    public string $identifier;

    public int $total;

    public int $amount_paid;
    
    public int $balance;

    public Carbon $issued_at;

    public array $payments = [];


    public function __construct(array $row)
    {
        if(isset($row['invoice_id'])) {
            $this->invoice_id = $row['invoice_id'];
            $this->contact_id = $row['contact_id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->identifier = $row['identifier'];
            $this->total = $row['total'];
            $this->amount_paid = $row['amount_paid'] ?: 0;
            $this->balance = $row['balance'] ?: 0;
            $this->issued_at = new Carbon($row['issued_at']);
        } else {
            error_log('!! Invalid row for '.self::class.': '.print_r($row, true));
            throw new \InvalidArgumentException(self::class.' domain object requires at least a PK value. See log for invalid values.');
        }
    }
}
