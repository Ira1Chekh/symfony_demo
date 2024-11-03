<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NoLinksOrClickbaitValidator extends ConstraintValidator
{
    private array $clickbaitWords = [
        'unbelievable', 'shocking', 'you won\'t believe', 'secret', 'top', 'amazing', 'incredible'
    ];

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoLinksOrClickbait) {
            return;
        }

        if (null === $value || '' === $value) {
            return; // Let other constraints handle empty values
        }

        // Check for web links
        if (preg_match('/https?:\/\/|www\./i', $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
            return;
        }

        // Check for clickbait words
        foreach ($this->clickbaitWords as $word) {
            if (stripos($value, $word) !== false) {
                $this->context->buildViolation($constraint->message)->addViolation();
                return;
            }
        }
    }
}