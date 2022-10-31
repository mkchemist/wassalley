<?php

namespace App\Services\Payments\PointPayments\Exceptions;

use Exception;

class InsufficientPointPaymentException extends Exception {

  public function __construct($message = "Insufficient points")
  {
    parent::__construct($message);
  }

}
