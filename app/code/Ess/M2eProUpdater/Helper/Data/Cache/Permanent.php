<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Helper\Data\Cache;

use \Ess\M2eProUpdater\Helper\Module;

class Permanent extends \Ess\M2eProUpdater\Helper\AbstractHelper
{
    /** @var \Magento\Framework\App\Cache */
    protected $cache;

    //########################################

    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($helperFactory, $context);
        $this->cache = $cache;
    }

    //########################################

    public function getValue($key)
    {
        $cacheKey = Module::IDENTIFIER .'_'. $key;
        $value = $this->cache->load($cacheKey);
        return $value === false ? NULL : unserialize($value);
    }

    public function setValue($key, $value, array $tags = array(), $lifeTime = NULL)
    {
        if ($value === NULL) {
            throw new \Exception('Can\'t store NULL value');
        }

        if (is_null($lifeTime) || (int)$lifeTime <= 0) {
            $lifeTime = 60*60*24;
        }

        $cacheKey = Module::IDENTIFIER .'_'. $key;

        $preparedTags = array(Module::IDENTIFIER .'_main');
        foreach ($tags as $tag) {
            $preparedTags[] = Module::IDENTIFIER .'_'. $tag;
        }

        $this->cache->save(serialize($value), $cacheKey, $preparedTags, (int)$lifeTime);
    }

    //########################################

    public function removeValue($key)
    {
        $cacheKey = Module::IDENTIFIER .'_'. $key;
        $this->cache->remove($cacheKey);
    }

    public function removeTagValues($tag)
    {
        $tags = array(Module::IDENTIFIER .'_'. $tag);
        $this->cache->clean($tags);
    }

    public function removeAllValues()
    {
        $this->removeTagValues('main');
    }

    //########################################
}