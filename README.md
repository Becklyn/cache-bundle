Becklyn Cache Bundle
====================

Provides a simple cache, that should be easy to use and high performance. Warning: for highly concurrent usage you should
either prepare the cache in advance or use symfony's own cache, as it has proper cache stampede protection.

Usage
-----


Fetch the simple cache factory and get the cache item:


```php
use Becklyn\Cache\Cache\SimpleCacheFactory;

class MyService
{
    private SimpleCacheItemInterface $cache;

    /**
     */
    public function __construct (SimpleCacheFactory $cacheFactory)
    {
        $this->cache = $cacheFactory->getItem(
            "my.cache.key",
            fn () => $this->loadItems()
        );
    }


    /**
     * Returns the cached or fresh items, depending on several conditions. 
     */
    public function getItems () : array
    {
        return $this->cache->get();
    }
    

    /**
     * Loads the items from the database
     */
    public function loadItems () : array
    {
        // ...
    }
}
```

Caching Based on Symfony Config
-------------------------------

You can either pass the cache key and generator to the `getItem()` method (like in the example above), 
or you can additionally pass in resources that should be tracked and can be used for cache invalidation.

If you pass resources, this will be a two-level cache:

1. first level will be the plain symfony/cache, which is really fast but has no proper way of cache invalidation.
2. second level will be the `ConfigCache` which will automatically be invalidated if some of the tracked resources change.

```php
$cacheFactory->getItem(
    "my.cache.key",
    fn () => $this->loadItems(),
    // eg. if your cache status depends on routing resources
    $this->router->getRouteCollection()->getResources()
 );
```  
