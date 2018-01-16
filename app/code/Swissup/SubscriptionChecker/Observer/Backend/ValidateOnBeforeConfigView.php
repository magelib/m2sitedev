<?php

namespace Swissup\SubscriptionChecker\Observer\Backend;

class ValidateOnBeforeConfigView implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Swissup\Core\Helper\PopupMessageManager
     */
    protected $popupMessageManager;

    /**
     * @var \Swissup\SubscriptionChecker\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    public function __construct(
        \Swissup\Core\Helper\PopupMessageManager $popupMessageManager,
        \Swissup\SubscriptionChecker\Helper\Data $helper,
        \Magento\Backend\Model\Session $session
    ) {
        $this->popupMessageManager = $popupMessageManager;
        $this->helper = $helper;
        $this->session = $session;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        // @see ValidateOnBeforeConfigSave::execute
        if ($this->session->getSwissupSkipValidation(true)) {
            return;
        }

        $section = $observer->getControllerAction()->getRequest()->getParam('section');
        if (!$this->helper->canValidateConfigSection($section)) {
            return;
        }

        $result = $this->helper->validateSubscription($section);
        if (is_array($result) && isset($result['error'])) {
            if (isset($result['response'])) {
                $this->popupMessageManager->addError(
                    $result['error'],
                    $result['response'],
                    'SwissUpLabs subscription validation response'
                );
            } else {
                $this->popupMessageManager->addError($result['error']);
            }
        }
    }
}
