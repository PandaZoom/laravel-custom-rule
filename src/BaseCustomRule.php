<?php

namespace PandaZoom\LaravelCustomRule;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Traits\Conditionable;
use InvalidArgumentException;
use function call_user_func;
use function collect;
use function is_callable;
use function is_null;

abstract class BaseCustomRule implements Rule, DataAwareRule, ValidatorAwareRule
{
    use Conditionable;

    /**
     * The validator performing the validation.
     *
     * @var \Illuminate\Contracts\Validation\Validator
     */
    protected $validator;

    /**
     * The data under validation.
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Additional validation rules that should be merged into the default rules during validation.
     *
     * @var array
     */
    protected array $customRules = [];

    /**
     * The failure messages, if any.
     *
     * @var array
     */
    protected array $messages = [];

    /**
     * The callback that will generate the "default" version of the rule.
     *
     * @var string|array|callable|null
     */
    public static $defaultCallback;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    abstract public function passes($attribute, $value): bool;

    /**
     * Set the default callback to be used for determining a default rules.
     *
     * If no arguments are passed, the default rule configuration will be returned.
     *
     * @param static|callable|null $callback
     * @return static|null
     */
    public static function defaults($callback = null): ?static
    {
        $output = null;

        if (is_null($callback)) {
            $output = static::default();
        }

        if (!is_callable($callback) && !($callback instanceof static)) {
            throw new InvalidArgumentException('The given callback should be callable or an instance of ' . static::class);
        }

        static::$defaultCallback = $callback;

        return $output;
    }

    /**
     * Get the default configuration of the rule as instance.
     *
     * @return Rule|static
     */
    public static function default(): Rule|static
    {
        $rule = is_callable(static::$defaultCallback)
            ? call_user_func(static::$defaultCallback)
            : static::$defaultCallback;

        return $rule instanceof Rule ? $rule : new static;
    }

    /**
     * Get the default configuration of the rule as list.
     *
     * @return array
     */
    public static function toArray(): array
    {
        return [static::default()];
    }

    /**
     * Get the default configuration of the rule and mark the field as required.
     *
     * @return array
     */
    public static function required(): array
    {
        return ['required', static::default()];
    }

    /**
     * Get the default configuration of the rule and mark the field as sometimes being required.
     *
     * @return array
     */
    public static function sometimes(): array
    {
        return ['sometimes', static::default()];
    }

    /**
     * Get the default configuration of the rule and mark the field as nullable.
     *
     * @return array
     */
    public static function nullable(): array
    {
        return ['nullable', static::default()];
    }

    /**
     * Set the performing validator.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @return static
     */
    public function setValidator($validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    /**
     * Set the data under validation.
     *
     * @param array $data
     * @return static
     */
    public function setData($data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Specify additional validation rules that should be merged with the default rules during validation.
     *
     * @param string|array $rules
     * @return static
     */
    public function rules(string|array $rules): static
    {
        $this->customRules = Arr::wrap($rules);

        return $this;
    }

    /**
     * Get the validation error message.
     *
     * @return array
     */
    public function message(): array
    {
        return $this->messages;
    }

    protected function resetMessages(): void
    {
        $this->messages = [];
    }

    protected function validate(string $attribute, array $rules): ValidatorContract
    {
        return Validator::make(
            $this->data,
            [$attribute => [...$rules, ...$this->customRules]],
            $this->validator->customMessages,
            $this->validator->customAttributes
        );
    }

    /**
     * Adds the given failures, and return false.
     *
     * @param array|string $messages
     * @return bool
     */
    protected function fail(array|string $messages): bool
    {
        $messages = collect(Arr::wrap($messages))
            ->map(fn($message) => $this->validator->getTranslator()->get($message))
            ->all();

        $this->messages = [...$this->messages, ...$messages];

        return false;
    }
}
