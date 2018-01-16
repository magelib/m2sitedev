<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Ess\M2eProUpdater\Helper\Config;

class InstallData implements InstallDataInterface
{
    /** @var \Ess\M2eProUpdater\Helper\Factory $helperFactory */
    private $helperFactory;

    /** @var \Ess\M2eProUpdater\Model\Factory $modelFactory */
    private $modelFactory;

    /** @var ModuleDataSetupInterface $installer */
    private $installer;

    //########################################

    public function __construct(
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        \Magento\Framework\Model\ResourceModel\Db\Context $dbContext
    ) {
        $this->helperFactory = $helperFactory;
        $this->modelFactory  = $modelFactory;
    }

    //########################################

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->installer = $setup;
        $this->installer->startSetup();

        try {

            /** @var \Ess\M2eProUpdater\Helper\Config $helper */
            $helper = $this->helperFactory->getObject('Config');
            $helper->setNotificationType(Config::NOTIFICATIONS_EXTENSION_PAGES);

        } catch (\Exception $exception) {}

        $this->installer->endSetup();
    }

    //########################################
}