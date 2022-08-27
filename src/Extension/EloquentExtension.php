<?php

namespace Yobidoyobi\Form\Extension;

use Yobidoyobi\Form\Extension\Eloquent\BelongsToManyType;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Form\AbstractExtension;
use Symfony\Component\Form\FormTypeInterface;

class EloquentExtension extends AbstractExtension
{
    /**
     * @return FormTypeInterface[]
     */
    #[Pure] protected function loadTypes(): array
    {
        return [
          new BelongsToManyType()
        ];
    }
}
