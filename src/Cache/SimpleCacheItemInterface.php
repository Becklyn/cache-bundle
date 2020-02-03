<?php declare(strict_types=1);

namespace Becklyn\Cache\Cache;

interface SimpleCacheItemInterface
{
    /**
     * Gets the cached value.
     *
     * @return mixed
     */
    public function get ();

    /**
     * Caches a new value.
     *
     * @param mixed $newValue
     */
    public function set ($newValue) : void;


    /**
     * Clears the item from the cache.
     */
    public function remove () : void;

    /**
     * Warms up the cache item, to ensure that the item will always be a hit.
     */
    public function warmup () : void;
}
