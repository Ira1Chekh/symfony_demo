<?php
// src/Validator/NoLinksOrClickbait.php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NoLinksOrClickbait extends Constraint
{
    public string $message = 'The summary cannot contain web links or clickbait words.';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}