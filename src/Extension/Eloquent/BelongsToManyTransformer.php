<?php

namespace Yobidoyobi\Form\Extension\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\DataTransformerInterface;

class BelongsToManyTransformer implements DataTransformerInterface
{
    /**
     * @throws TransformationFailedException
     */
    public function transform(mixed $value): mixed
    {
        if ($value instanceof BelongsToMany) {
            return $value->pluck($value->getOtherKey())->toArray();
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        return null;
    }
}
