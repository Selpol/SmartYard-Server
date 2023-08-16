<?php

class Validator
{
    private array $value;
    private array $rules;

    public function __construct(array $value, array $rules)
    {
        $this->value = $value;
        $this->rules = $rules;
    }

    /**
     * @return array
     * @throws RuleException
     */
    public function validate(): array
    {
        $keys = array_keys($this->rules);

        for ($i = 0; $i < count($keys); $i++)
            for ($j = 0; $j < count($this->rules[$keys[$i]]); $j++) {
                /** @var Rule $rule */
                $rule = $this->rules[$keys[$i]][$j];

                $this->value[$keys[$i]] = $rule->onRule($keys[$i], $this->value);
            }

        return $this->value;
    }
}

class RuleException extends Exception
{
    protected RuleMessage $validatorMessage;

    public function __construct(RuleMessage $validatorMessage, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->validatorMessage = $validatorMessage;
    }

    public function getValidatorMessage(): RuleMessage
    {
        return $this->validatorMessage;
    }
}

class RuleMessage
{
    private string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}

abstract class Rule
{
    protected string $message;

    protected function __construct(string $message)
    {
        $this->message = $message;
    }

    protected function getMessage(string $key): string
    {
        return sprintf($this->message, $key);
    }

    /**
     * @param string $key
     * @return RuleException
     */
    protected function toException(string $key): RuleException
    {
        $message = $this->getMessage($key);

        return new RuleException(new RuleMessage($message), $message);
    }

    /**
     * @throws RuleException
     */
    protected function filter(string $key, array $value, int $filter, array|int $options = null): mixed
    {
        if (!array_key_exists($key, $value) || is_null($value[$key]))
            return null;

        if ($options) {
            if (is_array($options)) {
                $options['flags'] = ($options['flags'] ?? 0) | FILTER_NULL_ON_FAILURE;

                $result = filter_var($value, $filter, $options);
            } else $result = filter_var($value, $filter, $options | FILTER_NULL_ON_FAILURE);
        } else $result = filter_var($value, $filter, FILTER_NULL_ON_FAILURE);

        if ($result == null)
            throw $this->toException($key);

        return $result;
    }

    /**
     * @param string $key
     * @param array $value
     * @return mixed
     * @throws RuleException
     */
    public abstract function onRule(string $key, array $value): mixed;

    public static function required(string $message = 'Поле %s обязательно для заполнения'): static
    {
        return new class($message) extends Rule {
            protected function __construct(string $message)
            {
                parent::__construct($message);
            }

            public function onRule(string $key, array $value): mixed
            {
                if (!array_key_exists($key, $value))
                    throw $this->toException($key);

                return $value[$key];
            }
        };
    }

    public static function nonNullable(string $message = 'Поле %s не может быть пустым'): static
    {
        return new class($message) extends Rule {
            public function onRule(string $key, array $value): mixed
            {
                if ($value[$key] == null)
                    throw $this->toException($key);

                return $value[$key];
            }
        };
    }

    public static function bool(string $message = 'Поле %s должно быть булевым значением'): static
    {
        return new class($message) extends Rule {
            public function onRule(string $key, array $value): mixed
            {
                return $this->filter($key, $value, FILTER_VALIDATE_BOOL);
            }
        };
    }

    public static function int(string $message = 'Поле %s должно быть челочисленным значением'): static
    {
        return new class($message) extends Rule {
            public function onRule(string $key, array $value): mixed
            {
                return $this->filter($key, $value, FILTER_VALIDATE_INT);
            }
        };
    }

    public static function float(string $message = 'Поле %s должно быть числом с плавающей точкой'): static
    {
        return new class($message) extends Rule {
            public function onRule(string $key, array $value): mixed
            {
                return $this->filter($key, $value, FILTER_VALIDATE_FLOAT);
            }
        };
    }

    public static function min(int|float $min, string $message = 'Поле %s меньше %d'): static
    {
        return new class($min, $message) extends Rule {
            private int|float $min;

            public function __construct(int|float $min, string $message)
            {
                parent::__construct($message);

                $this->min = $min;
            }

            protected function getMessage(string $key): string
            {
                return sprintf($this->message, $key, $this->min);
            }

            public function onRule(string $key, array $value): mixed
            {
                if (is_int($this->min))
                    return $this->filter($key, $value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $this->min]]);
                else if (is_float($this->min))
                    return $this->filter($key, $value, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => $this->min]]);

                return null;
            }
        };
    }

    public static function max(int|float $max, string $message = 'Поле %s больше %d'): static
    {
        return new class($max, $message) extends Rule {
            private int|float $max;

            public function __construct(int|float $max, string $message)
            {
                parent::__construct($message);

                $this->max = $max;
            }

            protected function getMessage(string $key): string
            {
                return sprintf($this->message, $key, $this->max);
            }

            public function onRule(string $key, array $value): mixed
            {
                if (is_int($this->max))
                    return $this->filter($key, $value, FILTER_VALIDATE_INT, ['options' => ['max_range' => $this->max]]);
                else if (is_float($this->max))
                    return $this->filter($key, $value, FILTER_VALIDATE_FLOAT, ['options' => ['max_range' => $this->max]]);

                return null;
            }
        };
    }

    public static function regexp(string $value, string $message = 'Поле %s должно быть определенного формата'): static
    {
        return new class($value, $message) extends Rule {
            private string $value;

            public function __construct(string $value, string $message)
            {
                parent::__construct($message);

                $this->value = $value;
            }

            public function onRule(string $key, array $value): mixed
            {
                return $this->filter($key, $value, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => $this->value]]);
            }
        };
    }

    public static function url(string $message = 'Поле %s должно быть формата ссылки', bool $path = false, bool $query = false): static
    {
        return new class($path, $query, $message) extends Rule {
            private bool $path;
            private bool $query;

            public function __construct(bool $path, bool $query, string $message)
            {
                parent::__construct($message);

                $this->path = $path;
                $this->query = $query;
            }

            public function onRule(string $key, array $value): mixed
            {
                if ($this->path || $this->query)
                    return $this->filter($key, $value, FILTER_VALIDATE_URL, ($this->path ? FILTER_FLAG_PATH_REQUIRED : 0) | ($this->query ? FILTER_FLAG_QUERY_REQUIRED : 0));

                return $this->filter($key, $value, FILTER_VALIDATE_URL);
            }
        };
    }

    public static function email(string $message = 'Поле %s должно быть формата почты'): static
    {
        return new class($message) extends Rule {
            public function onRule(string $key, array $value): mixed
            {
                return $this->filter($key, $value, FILTER_VALIDATE_EMAIL);
            }
        };
    }

    public static function ipV4(string $message = 'Поле %s должно быть формата ipV4'): static
    {
        return new class($message) extends Rule {
            public function onRule(string $key, array $value): mixed
            {
                return $this->filter($key, $value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
            }
        };
    }

    public static function ipV6(string $message = 'Поле %s должно быть формата ipV6'): static
    {
        return new class($message) extends Rule {
            public function onRule(string $key, array $value): mixed
            {
                return $this->filter($key, $value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
            }
        };
    }

    public static function mac(string $message = 'Поле %s должно быть формата MAC-адреса'): static
    {
        return new class($message) extends Rule {
            public function onRule(string $key, array $value): mixed
            {
                return $this->filter($key, $value, FILTER_VALIDATE_MAC);
            }
        };
    }

    public static function custom(callable $value, string $message = 'Поле %s не прошло проверку'): static
    {
        return new class($value, $message) extends Rule {
            /** @var callable $value */
            private $value;

            public function __construct(callable $value, string $message)
            {
                parent::__construct($message);

                $this->value = $value;
            }

            public function onRule(string $key, array $value): mixed
            {
                return call_user_func($this->value, [$key, $value]);
            }
        };
    }
}
