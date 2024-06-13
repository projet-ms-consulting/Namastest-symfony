<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TestConstraintValidator extends ConstraintValidator
{

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value->getTemplate() === null) {
            if ($value->getNom() === null || $value->getDescription() === null) {
                $this
                    ->context
                    ->buildViolation($constraint->message)
                    ->atPath('template')
                    ->addViolation();
            }
        }
    }
}