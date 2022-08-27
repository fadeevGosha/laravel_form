<?php

namespace Yobidoyobi\Form\Extension\Http;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\RequestHandlerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class FormTypeHttpExtension extends AbstractTypeExtension
{
    public function __construct(private ?RequestHandlerInterface $requestHandler = null)
    {
        $this->requestHandler = $requestHandler ?: new HttpRequestHandler();
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setRequestHandler($this->requestHandler);
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
