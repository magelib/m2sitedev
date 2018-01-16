<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\ShippingRules\Model\Plugin\Shipping\Rate\Result;

use Magento\Checkout\Model\Session;
use MageWorx\ShippingRules\Model\RulesApplier;
use MageWorx\ShippingRules\Model\Validator;

class Append
{
    /** @var Validator */
    protected $validator;

    /** @var Session|\Magento\Backend\Model\Session\Quote */
    protected $session;

    /** @var RulesApplier */
    protected $rulesApplier;

    /**
     * @param Validator $validator
     * @param RulesApplier $rulesApplier
     * @param Session $checkoutSession
     * @param \Magento\Backend\Model\Session\Quote $backendQuoteSession
     * @param \Magento\Framework\App\State $state
     * @internal param Session $session
     */
    public function __construct(
        Validator $validator,
        RulesApplier $rulesApplier,
        Session $checkoutSession,
        \Magento\Backend\Model\Session\Quote $backendQuoteSession,
        \Magento\Framework\App\State $state
    ) {
        $this->validator = $validator;
        $this->rulesApplier = $rulesApplier;
        if ($state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $this->session = $backendQuoteSession;
        } else {
            $this->session = $checkoutSession;
        }
    }

    /**
     * Validate each shipping method before append.
     * Apply the rules action if validation was successful.
     * Can mark some rules as disabled. The disabled rules will be removed in the class
     * @see MageWorx\ShippingRules\Observer\Sales\Quote\Address\CollectTotalsAfter
     * by checking the value of this mark in the rate object.
     *
     * NOTE: If you have some problems with the rules and the shipping methods, start debugging from here.
     *
     * @param \Magento\Shipping\Model\Rate\Result $subject
     * @param \Magento\Quote\Model\Quote\Address\RateResult\AbstractResult|\Magento\Shipping\Model\Rate\Result $result
     * @return array
     */
    public function beforeAppend($subject, $result)
    {
        if (!$result instanceof \Magento\Quote\Model\Quote\Address\RateResult\Method) {
            return [$result];
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->session->getQuote();

        $storeId = $quote->getStore()->getStoreId();
        $customerGroup = $quote->getCustomerGroupId();

        $this->validator->init($storeId, $customerGroup);
        if ($this->validator->validate($result)) {
            $rules = $this->validator->getAvailableRulesForRate($result);
            $result = $this->rulesApplier->applyRules($result, $rules);
        }

        return [$result];
    }
}
