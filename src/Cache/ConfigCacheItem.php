<?php declare(strict_types=1);

namespace Becklyn\Cache\Cache;

use Becklyn\Cache\Exception\MarshallingFailedException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;

class ConfigCacheItem extends CacheItem
{
    /** @var ConfigCacheFactoryInterface */
    private $configCacheFactory;

    /** @var string */
    private $cacheDir;

    /** @var CacheItemInterface */
    private $item;

    /** @var MarshallerInterface */
    private $marshaller;

    /** @var array */
    private $trackedResources;


    /**
     */
    public function __construct (
        CacheItemPoolInterface $pool,
        CacheItemInterface $item,
        ConfigCacheFactoryInterface $configCacheFactory,
        MarshallerInterface $marshaller,
        array $trackedResources,
        callable $generator,
        string $cacheDir,
        bool $isDebug
    )
    {
        parent::__construct($pool, $item, $generator, $isDebug);
        $this->configCacheFactory = $configCacheFactory;
        $this->cacheDir = $cacheDir;
        $this->item = $item;
        $this->marshaller = $marshaller;
        $this->trackedResources = $trackedResources;
    }


    /**
     * @inheritDoc
     */
    protected function generateValue ()
    {
        $cache = $this->configCacheFactory->cache(
            "{$this->cacheDir}/becklyn/cache/{$this->item->getKey()}.serialized",
            function (ConfigCacheInterface $cache) : void
            {
                $value = $this->marshaller->marshall([parent::generateValue()], $failed)[0];

                if (!empty($failed))
                {
                    throw new MarshallingFailedException("Marshalling the element failed.");
                }

                $cache->write($value, $this->trackedResources);
            }
        );

        return $this->marshaller->unmarshall(\file_get_contents($cache->getPath()));
    }


    /**
     * @inheritDoc
     */
    public function remove () : void
    {
        parent::remove();
        $this->removeConfigCache();
    }


    /**
     * @inheritDoc
     */
    public function warmup () : void
    {
        parent::warmup();

        $this->removeConfigCache();
        $this->generateValue();
    }


    /**
     *
     */
    private function removeConfigCache () : void
    {
        $cache = $this->configCacheFactory->cache(
            "{$this->cacheDir}/becklyn/cache/{$this->item->getKey()}.serialized",
            function () {}
        );

        @\unlink($cache->getPath());
    }

}
