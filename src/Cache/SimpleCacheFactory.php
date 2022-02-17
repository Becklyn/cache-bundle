<?php declare(strict_types=1);

namespace Becklyn\Cache\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;

class SimpleCacheFactory
{
    private CacheItemPoolInterface $appPool;
    private CacheItemPoolInterface $systemPool;
    private string $cacheDir;
    private bool $isDebug;
    private ?ConfigCacheFactoryInterface $configCacheFactory;
    private DefaultMarshaller $marshaller;


    public function __construct (
        CacheItemPoolInterface $appPool,
        CacheItemPoolInterface $systemPool,
        string $cacheDir,
        bool $isDebug
    )
    {
        $this->appPool = $appPool;
        $this->systemPool = $systemPool;
        $this->cacheDir = $cacheDir;
        $this->isDebug = $isDebug;
        $this->marshaller = new DefaultMarshaller();
    }


    public function getItem (string $key, callable $generator, ?array $resources = null) : SimpleCacheItemInterface
    {
        if (null !== $resources)
        {
            return new ConfigCacheItem(
                $this->systemPool,
                $this->systemPool->getItem($key),
                $this->getConfigCacheFactory(),
                $this->marshaller,
                $resources,
                $generator,
                $this->cacheDir,
                $this->isDebug
            );
        }

        return new CacheItem(
            $this->appPool,
            $this->appPool->getItem($key),
            $generator,
            $this->isDebug
        );
    }




    /**
     * Creates and returns a new config cache
     */
    private function getConfigCacheFactory () : ConfigCacheFactoryInterface
    {
        if (null === $this->configCacheFactory)
        {
            $this->configCacheFactory = new ConfigCacheFactory($this->isDebug);
        }

        return $this->configCacheFactory;
    }


    /**
     */
    public function setConfigCacheFactory (ConfigCacheFactoryInterface $factory) : void
    {
        $this->configCacheFactory = $factory;
    }
}
