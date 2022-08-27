<?php

namespace Yobidoyobi\Form\Extension\Session;

use JetBrains\PhpStorm\Pure;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;

class SessionTypeExtension extends AbstractTypeExtension
{
    #[Pure] public function __construct(
        private ?EventSubscriberInterface $eventSubscriber = null
    )
    {
        $this->eventSubscriber = $eventSubscriber ?? new SessionListener();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->eventSubscriber);
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
