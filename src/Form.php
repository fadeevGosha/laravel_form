<?php

namespace Yobidoyobi\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class Form extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults(
                [
                    'csrf_protection' => true,
                    'allow_extra_fields' => true,
                    'csrf_field_name' => 'csrf_token',
                ]
            );
    }
}

