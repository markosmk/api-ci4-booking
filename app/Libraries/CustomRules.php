<?php

namespace App\Libraries;

class CustomRules
{
    public function valid_time($value): bool
    {
        return preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/', $value);
    }
}
