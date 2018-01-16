<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Block\Adminhtml;

use Magento\Backend\Block\Widget;

abstract class AbstractBlock extends Widget
{
    /** @var \Ess\M2eProUpdater\Helper\Factory */
    protected $helperFactory;

    /** @var \Ess\M2eProUpdater\Model\Factory */
    protected $modelFactory;

    //########################################

    public function __construct(
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ){
        $this->helperFactory = $helperFactory;
        $this->modelFactory  = $modelFactory;

        parent::__construct($context, $data);
    }

    //########################################
}