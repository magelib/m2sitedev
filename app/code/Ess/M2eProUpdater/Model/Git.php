<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2016 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2eProUpdater\Model;

class Git extends \Ess\M2eProUpdater\Model\AbstractModel
{
    /** @var \Magento\Framework\HTTP\Client\Curl  */
    private $curlClient;

    /** @var \Magento\Framework\App\Filesystem\DirectoryList $directoryList */
    private $directoryList;

    /** @var \Magento\Framework\Filesystem\Directory\WriteFactory */
    private $directoryWriterFactory;

    const PACKAGE_OWNER = 'm2epro';
    const PACKAGE_NAME  = 'magento2-extension';

    //########################################

    public function __construct(
        \Magento\Framework\HTTP\Client\Curl $curlClient,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Directory\WriteFactory $directoryWriterFactory,
        \Ess\M2eProUpdater\Helper\Factory $helperFactory,
        \Ess\M2eProUpdater\Model\Factory $modelFactory,
        $data = []
    ) {
        parent::__construct($helperFactory, $modelFactory, $data);

        $this->curlClient    = $curlClient;
        $this->directoryList = $directoryList;
        $this->directoryWriterFactory = $directoryWriterFactory;
    }

    //########################################

    public function getLatestAvailableVersion()
    {
        $url = 'https://api.github.com/repos/' .self::PACKAGE_OWNER. '/' .self::PACKAGE_NAME. '/git/refs/tags';

        $this->curlClient->addHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.3; Trident/7.0; rv:11.0) like Gecko');
        $this->curlClient->get($url);

        if ($this->curlClient->getStatus() != 200) {
           return null;
        }

        $version = null;
        $info = \Zend_Json::decode($this->curlClient->getBody());

        foreach ($info as $item) {

            $tempVersion = isset($item['ref']) ? $item['ref'] : null;
            $tempVersion = str_replace('refs/tags/v', '', $tempVersion);

            if (!empty($tempVersion) && (version_compare($version, $tempVersion) == -1)) {
                $version = $tempVersion;
            }
        }

        return $version;
    }

    public function downloadZipPackage()
    {
        /** @var \Ess\M2eProUpdater\Helper\Module $moduleHelper */
        $moduleHelper = $this->helperFactory->getObject('Module');

        $url = 'https://github.com/' .self::PACKAGE_OWNER. '/' .self::PACKAGE_NAME. '/archive/master.zip';
        $sourceHandler = fopen($url, 'rb');

        $fileName = self::PACKAGE_NAME . '.zip';
        $targetFileHandler = fopen($moduleHelper->getTemporaryDirectoryPath() .'/'. $fileName, 'wb');

        while(!feof($sourceHandler)) {
            fwrite($targetFileHandler, fread($sourceHandler, 1024 * 8), 1024 * 8);
        }

        fclose($sourceHandler);
        fclose($targetFileHandler);
    }

    //########################################
}