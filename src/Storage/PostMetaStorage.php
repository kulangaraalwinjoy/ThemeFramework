<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Storage;

/**
 * Storage adapter for WordPress post meta.
 */
class PostMetaStorage implements StorageInterface
{
    /**
     * Meta key prefix.
     *
     * @var string
     */
    protected string $prefix;

    /**
     * Constructor.
     *
     * @param string $prefix
     */
    public function __construct(string $prefix = '_theme_')
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
     * Resolve the target post ID.
     *
     * @param mixed $objectId
     * @return int
     */
    protected function resolvePostId(mixed $objectId): int
    {
        if (!empty($objectId) && is_numeric($objectId)) {
            return (int) $objectId;
        }

        $currentId = get_the_ID();
        return $currentId !== false ? (int) $currentId : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $objectId = null, mixed $default = null): mixed
    {
        $postId = $this->resolvePostId($objectId);
        if ($postId === 0) {
            return $default;
        }

        $metaKey = $this->getMetaKey($key);

        if (!metadata_exists('post', $postId, $metaKey)) {
            return $default;
        }

        $value = get_post_meta($postId, $metaKey, true);
        return $value !== '' ? $value : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, mixed $objectId = null): bool
    {
        $postId = $this->resolvePostId($objectId);
        if ($postId === 0) {
            return false;
        }

        $metaKey = $this->getMetaKey($key);
        return (bool) update_post_meta($postId, $metaKey, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key, mixed $objectId = null): bool
    {
        $postId = $this->resolvePostId($objectId);
        if ($postId === 0) {
            return false;
        }

        $metaKey = $this->getMetaKey($key);
        return (bool) delete_post_meta($postId, $metaKey);
    }
}
