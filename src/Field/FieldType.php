<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Field;

use InvalidArgumentException;
use KulangaraAlwinJoy\ThemeFramework\Field\Contracts\FieldTypeInterface;

/**
 * Registry for available FieldType implementations.
 */
class FieldType
{
    /**
     * Registered field type classes or factory callables.
     *
     * @var array<string, class-string<FieldTypeInterface>|callable(): FieldTypeInterface>
     */
    protected static array $types = [];

    /**
     * Instantiated field type singletons.
     *
     * @var array<string, FieldTypeInterface>
     */
    protected static array $instances = [];

    /**
     * Register a new field type.
     *
     * @param string $type Identifier (e.g. 'text', 'media').
     * @param class-string<FieldTypeInterface>|callable(): FieldTypeInterface $handler
     * @return void
     */
    public static function register(string $type, string|callable $handler): void
    {
        self::$types[$type] = $handler;
        unset(self::$instances[$type]);
    }

    /**
     * Check if a field type is registered.
     *
     * @param string $type
     * @return bool
     */
    public static function has(string $type): bool
    {
        return isset(self::$types[$type]);
    }

    /**
     * Retrieve an instantiated field type handler.
     *
     * @param string $type
     * @return FieldTypeInterface
     * @throws InvalidArgumentException When field type is unknown.
     */
    public static function get(string $type): FieldTypeInterface
    {
        if (isset(self::$instances[$type])) {
            return self::$instances[$type];
        }

        if (!isset(self::$types[$type])) {
            throw new InvalidArgumentException(sprintf('Unknown field type: "%s".', $type));
        }

        $handler = self::$types[$type];

        if (is_callable($handler)) {
            $instance = $handler();
        } else {
            $instance = new $handler();
        }

        if (!($instance instanceof FieldTypeInterface)) {
            throw new InvalidArgumentException(
                sprintf('Field type handler for "%s" must implement FieldTypeInterface.', $type)
            );
        }

        self::$instances[$type] = $instance;
        return $instance;
    }

    /**
     * Get all registered type names.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return array_keys(self::$types);
    }

    /**
     * Reset all registered types (useful for test isolation).
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$types = [];
        self::$instances = [];
    }
}
