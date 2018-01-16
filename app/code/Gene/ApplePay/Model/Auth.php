<?php

namespace Gene\ApplePay\Model;

use Gene\ApplePay\Api\AuthInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Auth
 * @package Gene\ApplePay\Model
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Auth implements AuthInterface
{
    /**
     * @var \Gene\ApplePay\Api\Data\AuthDataInterfaceFactory
     */
    private $authData;

    /**
     * @var Ui\ConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Auth constructor.
     * @param \Gene\ApplePay\Api\Data\AuthDataInterfaceFactory $authData
     * @param Ui\ConfigProvider $configProvider
     * @param UrlInterface $url
     * @param CustomerSession $customerSession
     */
    public function __construct(
        \Gene\ApplePay\Api\Data\AuthDataInterfaceFactory $authData,
        Ui\ConfigProvider $configProvider,
        UrlInterface $url,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->authData = $authData;
        $this->configProvider = $configProvider;
        $this->url = $url;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManagerInterface;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        /** @var $data \Gene\ApplePay\Api\Data\AuthDataInterface */
        $data = $this->authData->create();
        $data->setClientToken($this->getClientToken());
        $data->setDisplayName($this->getDisplayName());
        $data->setActionSuccess($this->getActionSuccess());
        $data->setIsLoggedIn($this->getIsLoggedIn());
        $data->setStoreCode($this->getStoreCode());

        return $data;
    }

    protected function getClientToken()
    {
        return $this->configProvider->getClientToken();
    }

    protected function getDisplayName()
    {
        return $this->configProvider->getMerchantName();
    }

    protected function getActionSuccess()
    {
        return $this->url->getUrl('checkout/onepage/success', ['_secure' => true]);
    }

    protected function getIsLoggedIn()
    {
        return (bool) $this->customerSession->isLoggedIn();
    }

    protected function getStoreCode()
    {
        return $this->storeManager->getStore()->getCode();
    }
}
