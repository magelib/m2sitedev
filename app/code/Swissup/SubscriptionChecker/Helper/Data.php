<?php

namespace Swissup\SubscriptionChecker\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * Cache ID for last successfull check time
     */
    const LAST_CHECK_TIME_ID  = 'subscriptionchecker_lastcheck';

    /**
     * @var \Magento\Config\Model\Config\Structure
     */
    protected $configStructure;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cacheManager;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Config\Model\Config\Structure $configStructure,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->configStructure = $configStructure;
        $this->cacheManager = $cacheManager;
        $this->backendUrl = $backendUrl;
        $this->objectManager = $objectManager;
    }

    public function getFrequency()
    {
        return 24 * 3600;
    }

    /**
     * Retrieve Last update time
     *
     * @return int
     */
    public function getLastCheckTime()
    {
        return $this->cacheManager->load(self::LAST_CHECK_TIME_ID);
    }

    /**
     * Set last update time (now)
     *
     * @return $this
     */
    public function updateLastCheckTime()
    {
        $this->cacheManager->save(time(), self::LAST_CHECK_TIME_ID);
        return $this;
    }

    public function canValidateConfigSection($section)
    {
        $element = $this->configStructure->getElement($section);
        $tab = (string)$element->getAttribute('tab');

        $tabsToValidate = [
            'swissup'
        ];
        if (!in_array($tab, $tabsToValidate)) {
            return false;
        }

        if (($this->getFrequency() + $this->getLastCheckTime()) > time()) {
            return false;
        }

        $ignoredSections = $this->scopeConfig->getValue(
            'subscriptionchecker/ignore',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );

        if (!$ignoredSections) {
            return true;
        }
        foreach ($ignoredSections as $_section => $status) {
            if ($section === $_section) {
                return false;
            }
        }
        return true;
    }

    public function validateSubscription($configSection = false)
    {
        $model = $this->objectManager->create('Swissup\Core\Model\Module');
        $model->load('Swissup_Subscription');
        if ($configSection) {
            $model->setConfigSection($configSection);
        }

        $url = $this->backendUrl->getUrl('subscriptionchecker/subscription');
        $result = array();
        if (!$model->getIdentityKey()) {
            $result['error'] = __(
                'Please %1 SwissUpLabs subscription to use this module',
                sprintf("<a href='{$url}'>%s</a>", __('activate'))
            );
        } else {
            $result = $model->validateLicense();
            if (is_array($result) && isset($result['error'])) {
                $result['error'] = call_user_func_array('__', $result['error']);
                if (!isset($result['response'])) {
                    $result['error'] .= sprintf(
                        " (<a href='{$url}'>%s</a>)",
                        __('Open subscription activation page')
                    );
                }
            } else {
                $this->updateLastCheckTime();
            }
        }

        return $result;
    }
}
