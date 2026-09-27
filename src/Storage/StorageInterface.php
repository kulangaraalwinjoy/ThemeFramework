<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Storage;

/**
 * Contract for field value storage backends.
 */
interface StorageInterface
{
    /**
     * Retrieve a value from storage.
     *
     * @param string $key
     * @param mixed $objectId
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $objectId = null, mixed $default = null): mixed;

    /**
     * Persist a value to storage.
     *
     * @param string $key
     * @param mixed $value
     * @param mixed $objectId
     * @return bool
     */
    public function set(string $key, mixed $value, mixed $objectId = null): bool;

    /**
     * Remove a value from storage.
     *
     * @param string $key
     * @param mixed $objectId
     * @return bool
     */
    public function delete(string $key, mixed $objectId = null): bool;
}
