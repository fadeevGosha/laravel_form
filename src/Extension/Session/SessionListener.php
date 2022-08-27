<?php

namespace Yobidoyobi\Form\Extension\Session;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\ViewErrorBag;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;

class SessionListener implements EventSubscriberInterface
{

    #[ArrayShape([FormEvents::PRE_SET_DATA => "string"])]
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSet',
        ];
    }

    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();
        $rootName = $form->getRoot()->getName();
        $parent = $form->getParent();

        if ($parent
            && $form->getName() !== '_token'
            && !($parent->getConfig()->getType()->getInnerType() instanceof ChoiceType)
        ) {
            $name = $this->getDottedName($form);
            $fullName = $this->getFullName($rootName, $name);

            $oldValue = session()->getOldInput($fullName);
            if (!is_null($oldValue)) {
                try {
                    $value = $this->transformValue($event, $oldValue);
                    $event->setData($value);
                } catch (TransformationFailedException $e) {}
            }

            if ($errors = session('errors')) {
                /** @var ViewErrorBag $errors */
                if ($errors->has($name)) {
                    $form->addError(new FormError(implode(' ', $errors->get($name))));
                }
            }
        }
    }

    protected function getDottedName(FormInterface $form): string
    {
        $name = [$form->getName()];

        while ($form = $form->getParent()) {
            if ($form->getName() !== null && !$form->isRoot()) {
                array_unshift($name, $form->getName());
            }
        }

        return implode('.', $name);
    }

    protected function getFullName($rootName, $dottedName): string
    {
        if ($rootName === '') {
            return $dottedName;
        }

        return $rootName . '.' . $dottedName;
    }

    protected function transformValue(FormEvent $event, mixed $value): mixed
    {
        $config = $event->getForm()->getConfig();
        $dataClass = $config->getDataClass();

        if ($dataClass && is_array($value) && is_a($config->getDataClass(), Model::class, true)) {
            return new $dataClass;
        }

        if (is_array($value) && $event->getData() instanceof Collection) {
            $value = $event->getData()->make($value);
        }

        foreach ($config->getViewTransformers() as $transformer) {
            $value = $transformer->reverseTransform($value);
        }

        foreach ($config->getModelTransformers() as $transformer) {
            $value = $transformer->reverseTransform($value);
        }

        return $value;
    }
}
