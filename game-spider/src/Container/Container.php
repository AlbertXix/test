<?php

namespace GameSpider\Container;

use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];
    private array $aliases = [];

    public function set(string $id, callable|object $concrete): void
    {
        $this->bindings[$id] = $concrete;
        unset($this->instances[$id]);
    }

    public function singleton(string $id, callable|object $concrete): void
    {
        if (is_object($concrete) && !($concrete instanceof \Closure)) {
            $this->instances[$id] = $concrete;
        } else {
            $this->bindings[$id] = $concrete;
            $this->instances[$id] = null;
        }
    }

    public function alias(string $alias, string $id): void
    {
        $this->aliases[$alias] = $id;
    }

    public function get(string $id): mixed
    {
        $id = $this->resolveAlias($id);

        if (array_key_exists($id, $this->instances)) {
            if ($this->instances[$id] === null) {
                $this->instances[$id] = $this->build($this->bindings[$id]);
            }
            return $this->instances[$id];
        }

        if (array_key_exists($id, $this->bindings)) {
            return $this->build($this->bindings[$id]);
        }

        if (class_exists($id)) {
            return $this->autoWire($id);
        }

        throw new \RuntimeException("Service not found: {$id}");
    }

    public function has(string $id): bool
    {
        $id = $this->resolveAlias($id);
        return isset($this->bindings[$id])
            || isset($this->instances[$id])
            || class_exists($id);
    }

    private function build(callable $factory): mixed
    {
        return $factory($this);
    }

    private function autoWire(string $class): object
    {
        $ref = new \ReflectionClass($class);

        if (!$ref->isInstantiable()) {
            throw new \RuntimeException("Class not instantiable: {$class}");
        }

        $constructor = $ref->getConstructor();

        if ($constructor === null) {
            return new $class;
        }

        $params = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $params[] = $this->get($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $params[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException("Cannot resolve parameter: {$param->getName()} for {$class}");
            }
        }

        return $ref->newInstanceArgs($params);
    }

    private function resolveAlias(string $id): string
    {
        while (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        }
        return $id;
    }
}
