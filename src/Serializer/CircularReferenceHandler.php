<?php

namespace App\Serializer;

class CircularReferenceHandler
{
    public function handle($object)
    {
        // Replace circular reference with the ID of the object
        return $object->getId();
    }
}
