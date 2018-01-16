<?php

namespace Gene\ApplePay\Model\Auth;

use Gene\ApplePay\Api\Data\AuthDataInterface;

/**
 * Class Auth
 * @package Gene\ApplePay\Model\Auth
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Data implements AuthDataInterface
{
    /**
     * @var string
     */
    private $clientToken;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var string
     */
    private $actionSuccess;

    /**
     * @var bool
     */
    private $isLoggedIn;

    /**
     * @var string
     */
    private $storeCode;

    /**
     * @inheritdoc
     */
    public function getClientToken()
    {
        return $this->clientToken;
    }

    /**
     * @inheritdoc
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @inheritdoc
     */
    public function getActionSuccess()
    {
        return $this->actionSuccess;
    }

    /**
     * @inheritdoc
     */
    public function getIsLoggedIn()
    {
        return $this->isLoggedIn;
    }

    /**
     * @inheritdoc
     */
    public function getStoreCode()
    {
        return $this->storeCode;
    }

    /**
     * @inheritdoc
     */
    public function setClientToken($value)
    {
        $this->clientToken = $value;
    }

    /**
     * @inheritdoc
     */
    public function setDisplayName($value)
    {
        $this->displayName = $value;
    }

    /**
     * @inheritdoc
     */
    public function setActionSuccess($value)
    {
        $this->actionSuccess = $value;
    }

    /**
     * @inheritdoc
     */
    public function setIsLoggedIn($value)
    {
        $this->isLoggedIn = $value;
    }

    /**
     * @inheritdoc
     */
    public function setStoreCode($value)
    {
        $this->storeCode = $value;
    }
}
