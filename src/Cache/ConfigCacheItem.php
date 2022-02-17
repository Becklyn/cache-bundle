<?php declare(strict_types=1);

namespace Becklyn\Cache\Cache;

use Becklyn\Cache\Exception\MarshallingFailedException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Filesystem\Filesystem;

class ConfigCacheItem extends CacheItem
{
    private ConfigCacheFactoryInterface $configCacheFactory;
    private string $cacheDir;
    private CacheItemInterface $item;
    private MarshallerInterface $marshaller;
    private array $trackedResources;


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
     * @return mixed
     */
    protected function generateValue ()
    {
        return $this->marshaller->unmarshall(
            \file_get_contents(
                $this->getConfigCache()->getPath()
            )
        );
    }


    /**
     * Generates the config cache
     */
    private function getConfigCache () : ConfigCacheInterface
    {
        return $this->configCacheFactory->cache(
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
    }


    /**
     * @inheritDoc
     */
    public function remove () : void
    {
        $this->removeConfigCache();
        parent::remove();
    }


    /**
     * @inheritDoc
     */
    public function warmup () : void
    {
        $this->removeConfigCache();
        $this->generateValue();

        parent::warmup();
    }


    private function removeConfigCache () : void
    {
        $filesystem = new Filesystem();
        $filesystem->remove($this->getConfigCache()->getPath());
    }
}
