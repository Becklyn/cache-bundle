<?php declare(strict_types=1);

namespace Becklyn\Cache\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;

class CacheFactory
{
    /** @var CacheItemPoolInterface */
    private $appPool;

    /** @var CacheItemPoolInterface */
    private $systemPool;

    /** @var string */
    private $cacheDir;

    /** @var bool */
    private $isDebug;

    /** @var ConfigCacheFactoryInterface|null */
    private $configCacheFactory;

    /** @var DefaultMarshaller */
    private $marshaller;


    /**
     * @param CacheItemPoolInterface $appPool
     * @param CacheItemPoolInterface $systemPool
     * @param string                 $cacheDir
     * @param bool                   $isDebug
     */
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


    /**
     * @param string     $key
     * @param callable   $generator
     * @param array|null $resources
     */
    public function getItem (string $key, callable $generator, ?array $resources = null) : SimpleCacheItemInterface
    {
        if (!empty($resources))
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
