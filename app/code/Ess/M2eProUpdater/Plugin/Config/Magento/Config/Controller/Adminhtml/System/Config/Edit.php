<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Plugin\Config\Magento\Config\Controller\Adminhtml\System\Config;

class Edit extends \Ess\M2eProUpdater\Plugin\AbstractPlugin
{
    /** @var \Magento\Framework\ObjectManagerInterface $objectManager */
    protected $objectManager;

    //########################################

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory
    ) {
        parent::__construct($helperFactory, $modelFactory);

        $this->objectManager = $objectManager;
    }

    //########################################

    public function aroundExecute($interceptor, \Closure $callback)
    {
        $result = $callback();

        if ($result instanceof \Magento\Backend\Model\View\Result\Redirect) {
            return $result;
        }

        $result->getConfig()->addPageAsset('Ess_M2eProUpdater::css/help_block.css');
        $result->getConfig()->addPageAsset('Ess_M2eProUpdater::css/configuration/main.css');

        return $result;
    }

    //########################################
}