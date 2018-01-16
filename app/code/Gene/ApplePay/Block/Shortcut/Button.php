<?php

namespace Gene\ApplePay\Block\Shortcut;

use Gene\ApplePay\Block\AbstractButton;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\MethodInterface;

/**
 * Class Button
 * @package Gene\ApplePay\Block\Shortcut
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Button extends AbstractButton implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    const BUTTON_ELEMENT_INDEX = 'button_id';

    /**
     * @var DefaultConfigProvider
     */
    private $defaultConfigProvider;

    /**
     * Button constructor.
     * @param Context $context
     * @param Session $checkoutSession
     * @param MethodInterface $payment
     * @param \Gene\ApplePay\Model\Auth $auth
     * @param DefaultConfigProvider $defaultConfigProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        MethodInterface $payment,
        \Gene\ApplePay\Model\Auth $auth,
        DefaultConfigProvider $defaultConfigProvider,
        array $data = []
    ) {
        parent::__construct($context, $checkoutSession, $payment, $auth, $data);
        $this->defaultConfigProvider = $defaultConfigProvider;
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * @return string
     */
    public function getContainerId()
    {
        return $this->getData(self::BUTTON_ELEMENT_INDEX);
    }

    /**
     * Current Quote ID for guests
     * @return int
     */
    public function getQuoteId()
    {
        $config = $this->defaultConfigProvider->getConfig();
        if (!empty($config['quoteData']['entity_id'])) {
            return $config['quoteData']['entity_id'];
        }
        
        return 0;
    }
}
