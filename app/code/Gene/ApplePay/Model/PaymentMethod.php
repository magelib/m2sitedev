<?php

namespace Gene\ApplePay\Model;

use \Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PaymentMethod
 * @package Gene\ApplePay\Model
 * @author Aidan Threadgold <aidan@gene.co.uk>
 */
class PaymentMethod extends AbstractMethod
{
    /** @var string  */
    protected $_code = 'gene_applepay'; //@codingStandardsIgnoreLine
}