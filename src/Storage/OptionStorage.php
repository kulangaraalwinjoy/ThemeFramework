<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Storage;

/**
 * Storage adapter for WordPress Options.
 */
class OptionStorage implements StorageInterface
{
    /**
     * Option name prefix.
     *
     * @var string
     */
    protected string $prefix;

    /**
     * Constructor.
     *
     * @param string $prefix
     */
    public function __construct(string $prefix = '_theme_option_')
    {
        $this->prefix = $prefix;
    }

    /**
     * Get the formatted option name with prefix.
     *
     * @param string $key
     * @return string
     */
    public function getOptionName(string $key): string
    {
        return $this->prefix . $key;
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $objectId = null, mixed $default = null): mixed
    {
        $optionName = $this->getOptionName($key);
        return get_option($optionName, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, mixed $value, mixed $objectId = null): bool
    {
        $optionName = $this->getOptionName($key);
        return (bool) update_option($optionName, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key, mixed $objectId = null): bool
    {
        $optionName = $this->getOptionName($key);
        return (bool) delete_option($optionName);
    }
}
