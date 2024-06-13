<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class TestConstraint extends Constraint
{
    public $message = "Si le parent n'est pas rempli, le nom et la description sont obligatoires, et inversement.";

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

}