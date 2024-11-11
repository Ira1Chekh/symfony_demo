<?php

namespace App\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Loggable
{
    public function __construct(public string $action = 'default') {}
}
