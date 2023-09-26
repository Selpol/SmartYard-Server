<?php declare(strict_types=1);

namespace Selpol\Validator;

use Selpol\Validator\Exception\ValidatorException;

class Validator
{
    private array $value;
    private array $items;

    public function __construct(array $value, array $items)
    {
        $this->value = $value;
        $this->items = $items;
    }

    /**
     * @return array
     * @throws ValidatorException
     */
    public function validate(): array
    {
        $keys = array_keys($this->items);

        for ($i = 0; $i < count($keys); $i++)
            for ($j = 0; $j < count($this->items[$keys[$i]]); $j++) {
                /** @var Rule $item */
                $item = $this->items[$keys[$i]][$j];

                $this->value[$keys[$i]] = $item->onItem($keys[$i], $this->value);
            }

        return $this->value;
    }
}