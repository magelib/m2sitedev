<?php

namespace Gene\ApplePay\Model;

/**
 * Class Config
 * @package Gene\ApplePay\Model
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{

    protected $methodCode = 'gene_applepay';
    /**
     * Get merchant name to display
     * @return string
     */
    public function getMerchantName()
    {
        return $this->getValue('merchant_name');
    }
}
