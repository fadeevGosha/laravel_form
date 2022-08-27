<?php

namespace Yobidoyobi\Form\Extension;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormDefaultsTypeExtension extends AbstractTypeExtension
{
    protected array $defaults;

    public function __construct($defaults)
    {
        $this->defaults = $defaults;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        foreach ($this->defaults as $key => $default) {
            $resolver->setDefault($key, $default);
        }
    }

    public function getExtendedType(): string
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
