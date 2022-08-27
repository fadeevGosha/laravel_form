<?php

namespace Yobidoyobi\Form\Facade;

use Yobidoyobi\Form\FormRenderer as RealFormRenderer;
use Illuminate\Support\Facades\Facade;

class FormRenderer extends Facade
{
    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor(): string
    {
        return RealFormRenderer::class;
    }
}
