<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Helper\Data;

use \Ess\M2eProUpdater\Helper\Module;

class GlobalData extends \Ess\M2eProUpdater\Helper\AbstractHelper
{
    private $registryModel;

    //########################################

    public function __construct(
        \Magento\Framework\Registry $registryModel,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Magento\Framework\App\Helper\Context $context
    ){
        $this->registryModel = $registryModel;
        parent::__construct($helperFactory, $context);
    }

    //########################################

    public function getValue($key)
    {
        $globalKey = Module::IDENTIFIER .'_'. $key;
        return $this->registryModel->registry($globalKey);
    }

    public function setValue($key, $value)
    {
        $globalKey = Module::IDENTIFIER .'_'. $key;
        $this->registryModel->register($globalKey, $value, true);
    }

    //########################################

    public function unsetValue($key)
    {
        $globalKey = Module::IDENTIFIER .'_'. $key;
        $this->registryModel->unregister($globalKey);
    }

    //########################################
}