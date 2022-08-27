<?php

namespace Yobidoyobi\Form;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;

trait CreatesForms
{
    public function createForm(FormTypeInterface|string $type, mixed $data = null, array $options = []): FormInterface
    {
        return $this->getFormFactory()->create($type, $data, $options);
    }

    public function createNamed(
        ?string $name,
        FormTypeInterface|string $type = FormType::class,
        mixed $data = null,
        array $options = []
    ): FormInterface {
        return $this->getFormFactory()->createNamed($name, $type, $data, $options);
    }

    public function createFormBuilder(mixed $data = null, array $options = []): FormBuilderInterface
    {
        return $this->getFormFactory()->createNamedBuilder('', FormType::class, $data, $options);
    }

    public function createNamedFormBuilder(
        string $name = '',
        mixed $data = null,
        array $options = []
    ): FormBuilderInterface {
        return $this->getFormFactory()->createNamedBuilder($name, FormType::class, $data, $options);
    }

    protected function getFormFactory(): FormFactoryInterface
    {
        return app(FormFactoryInterface::class);
    }
}
