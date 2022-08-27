<?php

namespace Yobidoyobi\Form;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Form\Form;
use Illuminate\Foundation\Validation\ValidatesRequests;

trait ValidatesForms
{
    use ValidatesRequests;

    /**
     * @throws ValidationException
     */
    public function validateForm(Form $form, Request $request, array $rules, array $messages = array())
    {
        $data = $form->getName()
            ? $request->input($form->getName(), []) + $request->file($form->getName(), [])
            : $request->all();

        $validator = $this->getValidationFactory()->make($data, $rules, $messages);
        $validator->validate();
    }
}
