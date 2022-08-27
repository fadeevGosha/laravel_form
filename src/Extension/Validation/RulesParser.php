<?php

namespace Yobidoyobi\Form\Extension\Validation;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Laravel Validator rules to HTML5 attributes parser.
 *
 * Based on Laravel Validator and Former LiveValidation
 * @see https://github.com/laravel/framework
 * @see https://github.com/formers/former
 */
class RulesParser
{
    public function __construct(
        protected FormInterface $form,
        protected FormView $view,
        protected array $rules
    ) {}

    public function getAttributes(): array
    {
        $attributes = array();

        foreach ($this->rules as $rule) {
            list($rule, $parameters) = $this->parseRule($rule);

            if ($rule && method_exists($this, $rule)) {
                $attributes += $this->$rule($parameters);
            }
        }

        return $attributes;
    }

    /**
     * @see http://laravel.com/docs/5.1/validation#rule-accepted
     */
    #[ArrayShape(['required' => "string", 'title' => "string"])]
    protected function accepted(): array
    {
        return [
            'required' => 'required',
            'title' => $this->getTitle('accepted'),
        ];
    }

    /**
     * @see http://laravel.com/docs/5.1/validation#rule-required
     */
    #[ArrayShape(['required' => "string"])]
    protected function required(): array
    {
        return [
            'required' => 'required'
        ];
    }

    /**
     * Check that the input only contains alpha.
     *   alpha  --> pattern="[a-zA-Z]+"
     */
    #[ArrayShape(['pattern' => "string", 'title' => "string"])]
    protected function alpha(): array
    {
        return [
            'pattern' => '[a-zA-Z]+',
            'title' => $this->getTitle('alpha'),
        ];
    }

    /**
     * Check if the input contains only alpha and num.
     *   alpha_num  --> pattern="[a-zA-Z0-9]+"
     * @see http://laravel.com/docs/5.1/validation#rule-alpha-num
     */
    #[ArrayShape(['pattern' => "string", 'title' => "string"])]
    protected function alphaNum(): array
    {
        return [
            'pattern' => '[a-zA-Z0-9]+',
            'title' => $this->getTitle('alpha_num'),
        ];
    }

    /**
     * Check if the input contains only alpha, num and dash.
     *   alpha_dash  --> pattern="[a-zA-Z0-9_\-]+"
     * @see http://laravel.com/docs/5.1/validation#rule-alpha-dash
     */
    #[ArrayShape(['pattern' => "string", 'title' => "string"])]
    protected function alphaDash(): array
    {
        return [
            'pattern' => '[a-zA-Z0-9_\-]+',
            'title' => $this->getTitle('alpha_dash'),
        ];
    }

    /**
     * Check if the field is an integer value. Cannot contain decimals.
     *   integer  --> step="1" (number)
     *   integer  --> pattern="\d+" (text)
     * @see http://laravel.com/docs/5.1/validation#rule-integer
     */
    protected function integer(): array
    {
        if ($this->isNumeric()) {
            return ['step' => 1];
        }

        return [
            'pattern' => '\d+',
            'title' => $this->getTitle('integer'),
        ];
    }

    /**
     * Check that a field is numeric. It may contain decimals.
     *   numeric  --> step="any" (number)
     *   numeric  --> pattern="[-+]?[0-9]*[.,]?[0-9]+" (text)
     * @see http://laravel.com/docs/5.1/validation#rule-numeric
     */
    protected function numeric(): array
    {
        if ($this->isNumeric()) {
            return ['step' => 'any'];
        }

        return [
            'pattern' => '[-+]?[0-9]*[.,]?[0-9]+',
            'title' => $this->getTitle('numeric'),
        ];
    }

    /**
     * Check that a value is either 0 or 1, so it can be parsed as bool.
     *   boolean  --> pattern="0|1"
     * @see http://laravel.com/docs/5.1/validation#rule-boolean
     */
    #[ArrayShape(['pattern' => "string", 'title' => "string"])]
    protected function boolean(): array
    {
        return [
            'pattern' => '0|1',
            'title' => $this->getTitle('boolean'),
        ];
    }

    /**
     * Check that the value is numeric and contains exactly the given digits.
     *   digits:3  --> min="100" max="999"
     *   digits:3  --> pattern="\d{3,5}"  (text)
     * @see http://laravel.com/docs/5.1/validation#rule-digits
     */
    protected function digits(array $param): array
    {
        $digits = $param[0];

        if ($this->isNumeric()) {
            return [
                'min' => pow(10, $digits - 1),
                'max' => pow(10, $digits) - 1,
            ];
        }

        return [
            'pattern' => '\d{'.$digits.'}',
            'title' => $this->getTitle('digits', compact('digits')),
        ];
    }

    /**
     * Check that the value is numeric and contains between min/max digits.
     *   digits_between:3,5  --> min="100" max="99999"
     *   digits_between:3,5  --> pattern="\d{3,5}"  (text)
     * @see http://laravel.com/docs/5.1/validation#rule-digits-between
     */
    protected function digitsBetween(array $param): array
    {
        list($min, $max) = $param;

        if ($this->isNumeric()) {
            return [
                'min' => pow(10, $min - 1),
                'max' => pow(10, $max) - 1,
            ];
        }

        return [
            'pattern' => '\d{'.$min.','.$max.'}',
            'title' => $this->getTitle('digits_between', compact('min', 'max')),
        ];
    }

    /**
     * For numbers, set the minimum value.
     * For strings, set the minimum number of characters.
     *   min:5  --> min="5"       (number)
     *   min:5  --> minlength="5" (text)
     * @see http://laravel.com/docs/5.1/validation#rule-min
     */
    #[Pure]
    protected function min(array $param): array
    {
        $min = $param[0];

        if ($this->isNumeric()) {
            return ['min' => $min];
        }

        return [
            'minlength' => $min,
        ];
    }

    /**
     * For numbers, set the max value.
     * For strings, set the max number of characters.
     *   max:5  --> max="5"       (number)
     *   max:5  --> maxlength="5" (text)
     * @see http://laravel.com/docs/5.1/validation#rule-max
     */
    #[Pure] protected function max(array $param): array
    {
        $max = $param[0];

        if ($this->isNumeric()) {
            return ['max' => $max];
        }

        return ['maxlength' => $max];
    }

    /**
     * For number/range inputs, check if the number is between the values.
     * For strings, check the length of the string.
     *   between:3,5  --> min="3" max="5"             (number)
     *   between:3,5  --> minlength="3" maxlength="5" (text)
     * @see http://laravel.com/docs/5.1/validation#rule-between
     */
    #[Pure] protected function between(array $param): array
    {
        list ($min, $max) = $param;

        if ($this->isNumeric()) {
            return [
                'min' => $min,
                'max' => $max,
            ];
        }

        return [
            'minlength' => $min,
            'maxlength' => $max,
        ];
    }

    /**
     * For numbers: Check an exact value
     * For strings: Check the length of the string
     *   size:5 --> min="5" max="5" (number)
     *   size:5 --> pattern=".{5}"  (text)
     * @see http://laravel.com/docs/5.1/validation#rule-size
     */
    protected function size(array $param): array
    {
        $size = $param[0];

        if ($this->isNumeric()) {
            return [
                'min' => $size,
                'max' => $size,
                'title' => $this->getTitle('size.numeric', compact('size')),
            ];
        }

        return [
            'pattern' =>  '.{'.$size.'}',
            'title' => $this->getTitle('size.string', compact('size')),
        ];
    }

    /**
     * Check if the value is one of the give 'in' rule values
     * by creating a matching pattern.
     *   in:foo,bar  --> pattern="foo|bar"
     * @see http://laravel.com/docs/5.1/validation#rule-in
     */
    #[ArrayShape(['pattern' => "string", 'title' => "string"])]
    protected function in(array $params): array
    {
        return [
            'pattern' => implode('|', $params),
            'title' => $this->getTitle('in'),
        ];
    }

    /**
     * Check if the value is not one of the 'not_in' rule values
     * by creating a pattern value.
     *   not_in:foo,bar  --> pattern="(?:(?!^foo$|^bar$).)*"
     * @see http://laravel.com/docs/5.1/validation#rule-not-in
     */
    #[ArrayShape(['pattern' => "string", 'title' => "string"])]
    protected function notIn(array $params): array
    {
        return [
            'pattern' => '(?:(?!^' . join('$|^', $params) . '$).)*',
            'title' => $this->getTitle('not_in'),
        ];
    }

    /**
     * Set the 'min' attribute on a date/datetime/datetime-local field,
     * based on the 'before' validation.
     *   after:01-12-2015 -> min="2015-12-01"
     * @see http://laravel.com/docs/5.1/validation#rule-after
     */
    protected function after(array $params): array
    {
        if ($date = $this->getDateAttribute($params[0])) {
            return ['min' => $date];
        }

        return [];
    }

    /**
     * Set the 'min' attribute on a date/datetime/datetime-local field,
     * based on the 'before' validation.
     *   before:01-12-2015 -> max="2015-12-01"
     * @see http://laravel.com/docs/5.1/validation#rule-before
     */
    protected function before(array $params): array
    {
        if ($date = $this->getDateAttribute($params[0])) {
            return ['max' => $date];
        }

        return [];
    }

    /**
     * Add the image mime-type to a file input.
     * @see http://laravel.com/docs/5.1/validation#rule-image
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-accept
     */
    #[ArrayShape(['accept' => "string"])]
    protected function image(): array
    {
        return ['accept' => 'image/*'];
    }

    /**
     * Add the mime types to the accept attribute.
     *  mimes:xls,xlsx  --> accept=".xls, .xlsx"
     * @see http://laravel.com/docs/5.1/validation#rule-mimes
     * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input#attr-accept
     */
    #[ArrayShape(['accept' => "string"])]
    protected function mimes(array $param): array
    {
        $mimes = '.' . implode(', .', $param);

        return ['accept'  => $mimes];
    }

    protected function getTitle(string $rule, array $params = []): string
    {
        $params['attribute'] = $this->form->getName();

        return trans('validation.' . $rule, $params);
    }

    protected function isType(array $types): bool
    {
        $innerType = $this->form->getConfig()->getType()->getInnerType();

        foreach ($types as $type) {
            if ($innerType instanceof $type) {
                return true;
            }
        }

        return false;
    }

    protected function isNumeric(): bool
    {
        return in_array('numeric', $this->rules) || in_array('integer', $this->rules);
    }

    protected function getDateAttribute(string $dateStr): bool|string
    {
        $format = "Y-m-d";
        if ($this->isType([
                DateType::class,
                DateTimeType::class,
            ])) {
            $format .= '\TH:i:s';
        }

        return date($format, strtotime($dateStr));
    }

    /**
     * Methods below are copied from \Illuminate\Validation\Validator
     * @see https://github.com/laravel/framework/blob/5.1/src/Illuminate/Validation/Validator.php
     * @copyright Taylor Orwell
     */
    protected function parseRule($rules): array
    {
        if (is_array($rules)) {
            return $this->parseArrayRule($rules);
        }

        return $this->parseStringRule($rules);
    }

    protected function parseArrayRule(array $rules): array
    {
        return [Str::studly(trim(Arr::get($rules, 0))), array_slice($rules, 1)];
    }

    protected function parseStringRule(string $rules): array
    {
        $parameters = [];

        if (str_contains($rules, ':')) {
            list($rules, $parameter) = explode(':', $rules, 2);
            $parameters = $this->parseParameters($rules, $parameter);
        }

        return [Str::studly(trim($rules)), $parameters];
    }

    protected function parseParameters(string $rule, string $parameter): array
    {
        if (strtolower($rule) == 'regex') {
            return [$parameter];
        }

        return str_getcsv($parameter);
    }
}
