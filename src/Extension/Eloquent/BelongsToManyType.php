<?php

namespace Yobidoyobi\Form\Extension\Eloquent;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BelongsToManyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
          ->addModelTransformer(new BelongsToManyTransformer())
          ->addEventSubscriber(new BelongsToManyListener());
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
          'multiple' => true
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getName(): string
    {
        return 'belongs_to_many';
    }
}
