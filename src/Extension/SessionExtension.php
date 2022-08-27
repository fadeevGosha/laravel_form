<?php

namespace Yobidoyobi\Form\Extension;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\Form\AbstractExtension;

class SessionExtension extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    #[Pure]
    protected function loadTypeExtensions(): array
    {
        return array(
            new Session\CsrfTypeExtension,
            new Session\SessionTypeExtension,
        );
    }
}
