<?php

namespace App\Exceptions;

use Exception;

class BookingStateException extends Exception
{
    public function __construct(string $message = 'Booking state conflict.')
    {
        parent::__construct($message);
    }
}
