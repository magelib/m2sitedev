<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Plugin\Config\Magento\Config\Model;

use Ess\M2eProUpdater\Plugin\Config\Magento\Config\Model\Config\Structure\Data as ConfigStructure;

class Config extends \Ess\M2eProUpdater\Plugin\AbstractPlugin
{
    //########################################

    public function aroundSave(\Magento\Config\Model\Config $interceptor, \Closure $callback)
    {
        $saveData = $interceptor->getData();

        if (!isset($saveData['section']) || $saveData['section'] != ConfigStructure::INSTALLATION_UPGRADE_SECTION) {
            return $callback();
        }

        $groups = $saveData['groups'];

        /** @var \Ess\M2eProUpdater\Helper\Config $configHelper */
        $configHelper = $this->helperFactory->getObject('Config');

        if (isset($groups['settings']['fields']['notifications']['value'])) {

            $configHelper->setNotificationType(
                (int)$groups['settings']['fields']['notifications']['value']
            );

            unset($saveData['groups']['settings']['fields']['notifications']);
            $interceptor->setData($saveData);
        }

        return $callback();
    }

    //########################################
}