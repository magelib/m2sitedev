<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magmodules\GoogleShopping\Helper\General as GeneralHelper;

class Feed extends AbstractHelper
{

    const DEFAULT_FILENAME = 'google-shopping.xml';
    const DEFAULT_DIRECTORY = 'googleshopping';
    const DEFAULT_DIRECTORY_PATH = 'pub/media/googleshopping';

    const XML_PATH_GENERATE_ENABLED = 'magmodules_googleshopping/generate/enabled';
    const XML_PATH_FEED_URL = 'magmodules_googleshopping/feeds/url';
    const XML_PATH_FEED_RESULT = 'magmodules_googleshopping/feeds/results';
    const XML_PATH_FEED_FILENAME = 'magmodules_googleshopping/generate/filename';

    protected $general;
    protected $storeManager;
    protected $directory;
    protected $stream;
    protected $datetime;

    /**
     * Feed constructor.
     *
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @param Filesystem            $filesystem
     * @param DateTime              $datetime
     * @param General               $general
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        DateTime $datetime,
        GeneralHelper $general
    ) {
        $this->general = $general;
        $this->storeManager = $storeManager;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->datetime = $datetime;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getConfigData()
    {
        $feedData = [];
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $storeId = $store->getStoreId();
            $feedData[$storeId] = [
                'store_id'  => $storeId,
                'code'      => $store->getCode(),
                'name'      => $store->getName(),
                'is_active' => $store->getIsActive(),
                'status'    => $this->general->getStoreValue(self::XML_PATH_GENERATE_ENABLED, $storeId),
                'feed'      => $this->getFeedUrl($storeId),
                'result'    => $this->general->getStoreValue(self::XML_PATH_FEED_RESULT, $storeId),
            ];
        }
        return $feedData;
    }

    /**
     * @param $storeId
     *
     * @return mixed
     */
    public function getFeedUrl($storeId)
    {
        if ($location = $this->getFeedLocation($storeId)) {
            return $location['url'];
        }

        return false;
    }

    /**
     * @param        $storeId
     * @param string $type
     *
     * @return array
     */
    public function getFeedLocation($storeId, $type = '')
    {
        $fileName = $this->general->getStoreValue(self::XML_PATH_FEED_FILENAME, $storeId);

        if (empty($fileName)) {
            $fileName = self::DEFAULT_FILENAME;
        }

        if ($type == 'preview') {
            $extra = '-' . $storeId . '-preview.xml';
        } else {
            $extra = '-' . $storeId . '.xml';
        }

        if (substr($fileName, -3) != 'xml') {
            $fileName = $fileName . $extra;
        } else {
            $fileName = substr($fileName, 0, -4) . $extra;
        }

        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $feedUrl = $mediaUrl . self::DEFAULT_DIRECTORY;

        $location = [];
        $location['path'] = self::DEFAULT_DIRECTORY_PATH . '/' . $fileName;
        $location['url'] = $feedUrl . '/' . $fileName;
        $location['file_name'] = $fileName;
        $location['base_dir'] = self::DEFAULT_DIRECTORY_PATH;

        return $location;
    }

    /**
     * @param $storeId
     * @param $qty
     * @param $time
     * @param $date
     * @param $type
     */
    public function updateResult($storeId, $qty, $time, $date, $type)
    {
        if (empty($type)) {
            $type = 'manual';
        }
        $html = sprintf('Date: %s (%s) - Products: %s - Time: %s', $date, $type, $qty, $time);
        $this->general->setConfigData($html, self::XML_PATH_FEED_RESULT, $storeId);
    }

    /**
     * @param $row
     */
    public function writeRow($row)
    {
        $this->getStream()->write($row);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getStream()
    {
        if ($this->stream) {
            return $this->stream;
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('File handler unreachable'));
        }
    }

    /**
     * @param $config
     */
    public function createFeed($config)
    {
        $path = $config['feed_locations']['path'];
        $this->stream = $this->directory->openFile($path);

        $header = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL;
        $header .= '<rss xmlns:g="http://base.google.com/ns/1.0" version="2.0" encoding="utf-8">' . PHP_EOL;
        $header .= ' <channel>' . PHP_EOL;

        $this->getStream()->write($header);
    }

    /**
     * @param $summary
     */
    public function writeFooter($summary)
    {
        $footer = ' </channel>' . PHP_EOL;
        $footer .= $summary;
        $footer .= '</rss>' . PHP_EOL;
        $this->getStream()->write($footer);
    }

    /**
     * @param $time_start
     * @param $count
     * @param $limit
     *
     * @return array
     */
    public function getFeedSummary($time_start, $count, $limit)
    {
        $summary = [];
        $summary['system'] = 'Magento 2';
        $summary['extension'] = 'Magmodules_Googleshopping';
        $summary['version'] = $this->general->getExtensionVersion();
        $summary['magento_version'] = $this->general->getMagentoVersion();
        $summary['products'] = $count;
        $summary['limit'] = $limit;
        $summary['time'] = number_format((microtime(true) - $time_start), 2) . ' sec';
        $summary['date'] = $this->datetime->gmtDate();

        return $summary;
    }
}
