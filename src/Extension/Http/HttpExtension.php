<?php

namespace Yobidoyobi\Form\Extension\Http;

use Symfony\Component\Form\AbstractExtension;

class HttpExtension extends AbstractExtension
{
    protected function loadTypeExtensions(): array
    {
        return
            [
                new FormTypeHttpExtension(),
            ];
    }
}
