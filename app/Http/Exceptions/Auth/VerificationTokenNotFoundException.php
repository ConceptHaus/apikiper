<?php

namespace App\Http\Exceptions\Auth;

use Exception;

class VerificationTokenNotFoundException extends Exception{

    public function __construct($message=null) {
        parent::__construct($message, 0);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}