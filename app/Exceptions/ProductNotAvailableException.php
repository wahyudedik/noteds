<?php

namespace App\Exceptions;

use Exception;

class ProductNotAvailableException extends Exception
{
    protected $message = 'Product is not available for purchase.';
}
