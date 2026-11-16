<?php

namespace Wiznkingpro\Collect;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use ArrayIterator;
use JsonSerializable;
use Traversable;

class Collect implements Countable, IteratorAggregate, ArrayAccess, JsonSerializable
{
    protected array $items = [];

    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    protected function getArrayableItems($items): array
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof self) {
            return $items->all();
        }

        if ($items instanceof JsonSerializable) {
            return (array)$items->jsonSerialize();
        }

        if ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array)$items;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        return array_map(function ($item) {
            return $item instanceof self ? $item->toArray() : (is_object($item) && method_exists($item, 'toArray') ? $item->toArray() : $item);
        }, $this->items);
    }

    public function map(callable $callback): self
    {
        $keys = array_keys($this->items);
        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    public function filter(?callable $callback = null): self
    {
        if ($callback) {
            return new static(array_filter($this->items, $callback, ARRAY_FILTER_USE_BOTH));
        }

        return new static(array_filter($this->items));
    }

    public function each(callable $callback): self
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    public function push(...$values): self
    {
        foreach ($values as $value) {
            $this->items[] = $value;
        }

        return $this;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function first(?callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($this->items)) {
                return $default;
            }

            foreach ($this->items as $item) {
                return $item;
            }
        }

        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    public function last(?callable $callback = null, $default = null)
    {
        return (new static(array_reverse($this->items, true)))->first($callback, $default);
    }

    public function pluck(string $value, ?string $key = null): self
    {
        $results = [];

        foreach ($this->items as $item) {
            $itemValue = is_array($item) ? ($item[$value] ?? null) : ($item->{$value} ?? null);

            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);
                $results[$itemKey] = $itemValue;
            }
        }

        return new static($results);
    }

    public function groupBy(string $groupBy): self
    {
        $results = [];

        foreach ($this->items as $key => $value) {
            $groupKey = is_array($value) ? ($value[$groupBy] ?? null) : ($value->{$groupBy} ?? null);

            if (!isset($results[$groupKey])) {
                $results[$groupKey] = new static();
            }

            $results[$groupKey]->push($value);
        }

        return new static($results);
    }

    public function keys(): self
    {
        return new static(array_keys($this->items));
    }

    public function values(): self
    {
        return new static(array_values($this->items));
    }

    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $this->items[$key];
        }

        return $default;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function contains($key, $value = null): bool
    {
        if (func_num_args() === 2) {
            return $this->contains(function ($item) use ($key, $value) {
                $actual = is_array($item) ? ($item[$key] ?? null) : ($item->{$key} ?? null);
                return $actual == $value;
            });
        }

        if (is_callable($key)) {
            return !is_null($this->first($key));
        }

        return in_array($key, $this->items, true);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet($offset, $value): void
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset): void
    {
        unset($this->items[$offset]);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
