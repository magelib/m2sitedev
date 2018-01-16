<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * Model factory
 */
namespace Ess\M2eProUpdater\Model;

class Factory
{
    protected $helperFactory;
    protected $objectManager;

    //########################################

    /**
     * Construct
     *
     * @param \Ess\M2eProUpdater\Helper\Factory $helperFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->helperFactory = $helperFactory;
        $this->objectManager = $objectManager;
    }

    //########################################

    /**
     * @param $modelName
     * @param array $arguments
     * @return \Ess\M2eProUpdater\Model\AbstractModel
     * @throws \Exception
     */
    public function getObject($modelName, array $arguments = [])
    {
        $model = $this->objectManager->create('\Ess\M2eProUpdater\Model\\'.$modelName, $arguments);

        if (!$model instanceof \Ess\M2eProUpdater\Model\AbstractModel) {
            throw new \Exception(
                __('%1 doesn\'t extends \Ess\M2eProUpdater\Model\AbstractModel', $modelName)
            );
        }

        return $model;
    }

    //########################################
}