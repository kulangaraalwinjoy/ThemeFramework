<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Storage;

/**
 * Storage adapter for WordPress menu item meta.
 */
class MenuMetaStorage implements StorageInterface
{
    /**
     * Meta key prefix for menu items.
     *
     * @var string
     */
    protected string $prefix;

    /**
     * Constructor.
     *
     * @param string $prefix
     */
    public function __construct(string $prefix = '_theme_menu_')
    {
        $this->prefix = $prefix;
    }

    /**
     * Get the formatted meta key with prefix.
     *
     * @param string $key
     * @return string
     */
    public function getMetaKey(string $key): string
    {
        return $this->prefix . $key;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $objectId = null, mixed $default = null): mixed
    {
        $menuItemId = (int) $objectId;
        if ($menuItemId === 0) {
            return $default;
        }

        $metaKey = $this->getMetaKey($key);

        if (!metadata_exists('post', $menuItemId, $metaKey)) {
            // Fallback to check unprefixed key if prefixed does not exist
            if (metadata_exists('post', $menuItemId, $key)) {
                $fallback = get_post_meta($menuItemId, $key, true);
                return $fallback !== '' ? $fallback : $default;
            }
            return $default;
        }

        $value = get_post_meta($menuItemId, $metaKey, true);
        return $value !== '' ? $value : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, mixed $objectId = null): bool
    {
        $menuItemId = (int) $objectId;
        if ($menuItemId === 0) {
            return false;
        }

        $metaKey = $this->getMetaKey($key);
        return (bool) update_post_meta($menuItemId, $metaKey, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key, mixed $objectId = null): bool
    {
        $menuItemId = (int) $objectId;
        if ($menuItemId === 0) {
            return false;
        }

        $metaKey = $this->getMetaKey($key);
        return (bool) delete_post_meta($menuItemId, $metaKey);
    }
}
