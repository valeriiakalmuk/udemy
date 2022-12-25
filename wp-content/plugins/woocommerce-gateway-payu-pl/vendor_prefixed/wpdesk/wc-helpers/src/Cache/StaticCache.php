<?php

namespace WGPayuVendor\WPDesk\Cache;

class StaticCache implements \Psr\SimpleCache\CacheInterface
{
    /** @var array */
    static $cache = [];
    /**
     * @param iterable $keys
     * @param null $default
     *
     * @return Generator|iterable
     */
    public function getMultiple($keys, $default = null)
    {
        foreach ($keys as $key) {
            (yield $this->get($key, $default));
        }
    }
    /**
     * @param string $key
     * @param null $default
     *
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return self::$cache[$key];
        } else {
            return $default;
        }
    }
    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return isset(self::$cache[$key]);
    }
    /**
     * @param iterable $values
     * @param int $ttl
     *
     * @return bool
     */
    public function setMultiple($values, $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return \true;
    }
    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     *
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        if (!empty($key)) {
            self::$cache[$key] = $value;
        }
        return \true;
    }
    /**
     * @param iterable $keys
     *
     * @return bool
     */
    public function deleteMultiple($keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return \true;
    }
    /**
     * @param string $key
     *
     * @return bool|void
     */
    public function delete($key)
    {
        unset(self::$cache[$key]);
    }
    /**
     * @return bool
     */
    public function clear()
    {
        self::$cache = [];
        return \true;
    }
}
