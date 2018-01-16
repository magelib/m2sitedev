<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Helper;

class Factory
{
    protected $objectManager;

    //########################################

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->objectManager = $objectManager;
    }

    //########################################

    /**
     * @param $helperName
     * @param array $arguments
     * @return \Magento\Framework\App\Helper\AbstractHelper
     * @throws \Exception
     */
    public function getObject($helperName, array $arguments = [])
    {
        $helper = $this->objectManager->get('\Ess\M2eProUpdater\Helper\\'.$helperName, $arguments);

        if (!$helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \Exception(
                __('%1 doesn\'t extends \Magento\Framework\App\Helper\AbstractHelper', $helperName)
            );
        }

        return $helper;
    }

    //########################################
}