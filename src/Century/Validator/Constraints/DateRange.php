<?php

namespace Century\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class DateRange extends Constraint
{
    public $message = "The date must be between the 1st Jan this year and today.";
}