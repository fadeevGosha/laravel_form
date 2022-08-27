<?php

namespace Yobidoyobi\Form;

use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\FormView;

class FormRenderer
{
    public function __construct(protected FormRendererInterface $renderer)
    {
    }

    public function form(FormView $view, array $variables = []): string
    {
        return $this->renderer->renderBlock($view, 'form', $variables);
    }

    public function start(FormView $view, array $variables = []): string
    {
        return $this->renderer->renderBlock($view, 'form_start', $variables);
    }

    public function end(FormView $view, array $variables = []): string
    {
        return $this->renderer->renderBlock($view, 'form_end', $variables);
    }

    public function widget(FormView $view, array $variables = []): string
    {
        return $this->renderer->searchAndRenderBlock($view, 'widget', $variables);
    }

    public function errors(FormView $view, array $variables = []): string
    {
        return $this->renderer->searchAndRenderBlock($view, 'errors', $variables);
    }

    public function label(FormView $view, $label, array $variables = []): string
    {
        if (!isset($variables['label'])) {
            $variables['label'] = $label;
        }

        return $this->renderer->searchAndRenderBlock($view, 'label', $variables);
    }

    public function row(FormView $view, array $variables = []): string
    {
        return $this->renderer->searchAndRenderBlock($view, 'row', $variables);
    }

    public function rest(FormView $view, array $variables = []): string
    {
        return $this->renderer->searchAndRenderBlock($view, 'rest', $variables);
    }
}
