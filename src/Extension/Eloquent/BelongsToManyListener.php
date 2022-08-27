<?php

namespace Yobidoyobi\Form\Extension\Eloquent;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BelongsToManyListener implements EventSubscriberInterface
{
    #[ArrayShape([FormEvents::PRE_SUBMIT => "string"])]
    public static function getSubscribedEvents(): array
    {
        return
            [
                FormEvents::PRE_SUBMIT => 'preSubmit',
            ];
    }

    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();

        if ($parent = $form->getParent()) {
            $parent->remove($form->getName());
        }
    }
}
