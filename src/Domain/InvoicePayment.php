<?php

namespace Collegeplannerpro\InterviewReport\Domain;

use Carbon\Carbon;

class InvoicePayment
{
    public int $payment_id;

    public int $invoice_id;

    public int $amount;

    public Carbon $paid_at;

    public function __construct(array $row)
    {
        if(isset($row['payment_id'])) {
            $this->payment_id = $row['payment_id'];
            $this->invoice_id = $row['invoice_id'];
            $this->amount = $row['amount'];
            $this->paid_at = new Carbon($row['paid_at']);
        } else {
            error_log('!! Invalid row for '.self::class.': '.print_r($row, true));
            throw new \InvalidArgumentException(self::class.' domain object requires at least a PK value. See log for invalid values.');
        }
    }
}
