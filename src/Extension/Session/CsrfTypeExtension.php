<?php

namespace Yobidoyobi\Form\Extension\Session;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\AbstractTypeExtension;

class CsrfTypeExtension extends AbstractTypeExtension
{
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (!$view->parent && $options['compound']) {
            $factory = $form->getConfig()->getFormFactory();

            $csrfForm = $factory->createNamed('_token', HiddenType::class, csrf_token(), array(
                'mapped' => false,
            ));

            $view->children['_token'] = $csrfForm->createView(null);
        }
    }

    public function getExtendedType(): string
    {
        return FormType::class;
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
