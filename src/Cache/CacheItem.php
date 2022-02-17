<?php declare(strict_types=1);

namespace Becklyn\Cache\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CacheItem implements SimpleCacheItemInterface
{
    private CacheItemPoolInterface $pool;
    private CacheItemInterface $item;
    /** @var callable */
    private $generator;
    private bool $isDebug;


    public function __construct (
        CacheItemPoolInterface $pool,
        CacheItemInterface $item,
        callable $generator,
        bool $isDebug
    )
    {
        $this->pool = $pool;
        $this->item = $item;
        $this->generator = $generator;
        $this->isDebug = $isDebug;
    }


    /**
     * @return mixed
     */
    public function get ()
    {
        if ($this->isDebug)
        {
            return $this->generateValue();
        }

        if ($this->item->isHit())
        {
            return $this->item->get();
        }

        $newValue = $this->generateValue();
        $this->set($newValue);
        return $newValue;
    }


    /**
     * @inheritDoc
     */
    public function set ($newValue) : void
    {
        $this->pool->save(
            $this->item->set($newValue)
        );
    }


    /**
     * @inheritDoc
     */
    public function remove () : void
    {
        $this->pool->deleteItem($this->item->getKey());
    }


    /**
     * @inheritDoc
     */
    public function warmup () : void
    {
        $this->set($this->generateValue());
    }


    /**
     * Generates a new value
     *
     * @return mixed
     */
    protected function generateValue ()
    {
        return ($this->generator)();
    }
}
