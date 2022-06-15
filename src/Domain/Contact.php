<?php

namespace Collegeplannerpro\InterviewReport\Domain;

use Carbon\Carbon;

class Contact
{
    public int $contact_id;

    public string $first_name;

    public string $last_name;

    public string $email;

    public string $street_address;

    public string $city;
    
    public string $state_code;

    public string $postal_code;

    public string $phone;

    public string $country_code;

    public function __construct(array $row)
    {
        if(!is_null($row['contact_id'])) {
            $this->invoice_id = $row['contact_id'];
            $this->first_name = $row['first_name'];
            $this->last_name = $row['last_name'];
            $this->email = $row['email'];
            $this->street_address = $row['street_address'];
            $this->city = $row['city'];
            $this->state_code = $row['state_code'];
            $this->postal_code = $row['postal_code'];
            $this->phone = $row['phone'];
            $this->country_code = $row['country_code'];
        } else {
            error_log('!! Invalid row for '.self::class.': '.print_r($row, true));
            throw new \InvalidArgumentException(self::class.' domain object requires at least a PK value. See log for invalid values.');
        }
    }
}
