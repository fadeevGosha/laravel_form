<?php

namespace Yobidoyobi\Form\Extension\Validation;

use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

class ValidationListener implements EventSubscriberInterface
{
    protected mixed $data;

    public function __construct(protected ValidationFactory $validator) {}

    /**
     * {@inheritdoc}
     */
    #[ArrayShape([FormEvents::PRE_SUBMIT => "string", FormEvents::POST_SUBMIT => "string"])]
    public static function getSubscribedEvents(): array
    {
        return array(
                FormEvents::PRE_SUBMIT => 'gatherData',
                FormEvents::POST_SUBMIT => 'validateRules',
            );
    }

    public function gatherData(FormEvent $event)
    {
        $this->data = $event->getData();
    }

    public function validateRules(FormEvent $event)
    {
        if ($event->getForm()->isRoot()) {
            $root = $event->getForm();

            $rules = $this->findRules($root);
            $validator = $this->validator->make($this->data ?: [], $rules);

            if ($validator->fails()) {
                foreach ($validator->getMessageBag()->toArray() as $name => $messages) {
                    foreach ($messages as $message) {
                        $form = $this->getByDotted($root, $name);
                        $form->addError(new FormError($message));
                    }
                }
            }
        }
    }

    protected function findRules(
        FormInterface $parent,
        array $rules = [],
        $parentName = null
    ): array
    {
        foreach ($parent->all() as $form) {
            $config = $form->getConfig();
            $name = $form->getName();
            $innerType = $form->getConfig()->getType()->getInnerType();

            if ($config->hasOption('rules')) {
                if ($parentName !== null) {
                    $name = $parentName . '.' . $name;
                } elseif (! $parent->isRoot()) {
                    $name = $parent->getName() . '.' . $name;
                }

                $rules[$name] = $this->addTypeRules($innerType, $config->getOption('rules'));
            }

            if ($innerType instanceof CollectionType) {
                $children = $form->all();
                if (isset($children[0])) {
                    $rules = $this->findRules($children[0], $rules, $name . '.*');
                }
            }
        }

        return $rules;
    }

    protected function getByDotted(FormInterface $form, $name): FormInterface
    {
        $parts = explode('.', $name);

        while ($name = array_shift($parts)) {
            $form = $form->get($name);
        }

        return $form;
    }

    protected function addTypeRules(FormTypeInterface $type, array $rules): array
    {
        if (($type instanceof NumberType || $type instanceof IntegerType)
            && !in_array('numeric', $rules)
        ) {
            $rules[] = 'numeric';
        }

        if (($type instanceof EmailType)
            && !in_array('email', $rules)
        ) {
            $rules[] = 'email';
        }

        if (($type instanceof TextType || $type instanceof TextareaType)
            && !in_array('string', $rules)
        ) {
            $rules[] = 'string';
        }

        return $rules;
    }
}
