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
            "{$this->cacheDir}/becklyn/cache/{$this->item->getKey()}",
            function (ConfigCacheInterface $cache) : void
            {
                $value = \var_export(
                    $this->marshaller->marshall([parent::generateValue()], $failed)
                );

                if (!empty($failed))
                {
                    throw new MarshallingFailedException("Marshalling the element failed.");
                }

                $cache->write(
                    "<?php return {$value};",
                    $this->trackedResources
                );
            }
        );

        return $this->marshaller->unmarshall(\file_get_contents($cache->getPath()))[0] ?? null;
    }
}
