<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<int, string>
 */
class IpToLongTransformer implements DataTransformerInterface
{
    /**
     * Transforms an object to a string (id).
     */
    public function transform($value): string
    {
        if (null === $value) {
            return '';
        }

        return long2ip($value);
    }

    /**
     * Transforms an id to an object.
     */
    public function reverseTransform($value): int
    {
        if (null === $value) {
            return 0;
        }

        $value = ip2long($value);
        if ($value === false) {
            return 0;
        }

        return $value;
    }
}
