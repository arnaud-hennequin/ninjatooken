<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class IpToLongTransformer implements DataTransformerInterface
{

    /**
     * Transforms an object to a string (id).
     *
     * @param null $value
     * @return string
     */
    public function transform($value = null): string
    {
        if (null === $value) {
            return "";
        }

        return long2ip($value);
    }

    /**
     * Transforms an id to an object.
     *
     * @param null $value
     * @return int
     */
    public function reverseTransform($value = null): int
    { 
        if (!$value) {
            return false;
        }
        return ip2long($value);
    }
}