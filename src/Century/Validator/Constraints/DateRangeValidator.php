<?php

namespace Century\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class DateRangeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        //echo $value;
        $date = new \DateTime($value);
        $year = date('Y');
        $to = new \DateTime();
        $to->setTime(0,0,0);


        if ($date->format('Y') != $year || $date > $to) {
            $this->context->addViolation($constraint->message);
        }
    }
}