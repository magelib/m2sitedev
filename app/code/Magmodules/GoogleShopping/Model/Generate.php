<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magmodules\GoogleShopping\Model;

use Magmodules\GoogleShopping\Model\Products as ProductsModel;
use Magmodules\GoogleShopping\Helper\Source as SourceHelper;
use Magmodules\GoogleShopping\Helper\Product as ProductHelper;
use Magmodules\GoogleShopping\Helper\General as GeneralHelper;
use Magmodules\GoogleShopping\Helper\Feed as FeedHelper;

use Psr\Log\LoggerInterface;

class Generate
{

    const XML_PATH_FEED_RESULT = 'magmodules_googleshopping/feeds/results';
    const XML_PATH_GENERATE = 'magmodules_googleshopping/generate/enable';

    protected $products;
    protected $source;
    protected $product;
    protected $general;
    protected $feed;

    /**
     * Generate constructor.
     * @param Products $products
     * @param SourceHelper $source
     * @param ProductHelper $product
     * @param GeneralHelper $general
     * @param FeedHelper $feed
     * @param LoggerInterface $logger
     */
    public function __construct(
        ProductsModel $products,
        SourceHelper $source,
        ProductHelper $product,
        GeneralHelper $general,
        FeedHelper $feed,
        LoggerInterface $logger
    ) {
        $this->products = $products;
        $this->source = $source;
        $this->product = $product;
        $this->general = $general;
        $this->feed = $feed;
        $this->logger = $logger;
    }

    /**
     * Generate all feeds
     */
    public function generateAll()
    {
        $storeIds = $this->general->getEnabledArray(self::XML_PATH_GENERATE);
        foreach ($storeIds as $storeId) {
            $this->generateByStore($storeId, 'cron');
        }
    }

    /**
     * @param $storeId
     * @param string $type
     * @return array
     */
    public function generateByStore($storeId, $type = 'manual')
    {
        $timeStart = microtime(true);
        $config = $this->source->getConfig($storeId, $type);
        $this->feed->createFeed($config);
        $products = $this->products->getCollection($config);
        $relations = $config['filters']['relations'];
        $limit = $config['filters']['limit'];
        $count = 0;

        foreach ($products as $product) {
            $parent = '';
            if ($relations) {
                if ($parentId = $this->product->getParentId($product->getEntityId())) {
                    $parent = $products->getItemById($parentId);
                }
            }
            if ($dataRow = $this->product->getDataRow($product, $parent, $config)) {
                if ($row = $this->source->reformatData($dataRow, $product, $config)) {
                    $this->feed->writeRow($row);
                    $count++;
                }
            }
        }

        $summary = $this->feed->getFeedSummary($timeStart, $count, $limit);
        $footer = $this->source->getXmlFromArray($summary, 'config');
        $this->feed->writeFooter($footer);
        $this->feed->updateResult($storeId, $count, $summary['time'], $summary['date'], $type);

        return [
            'status' => 'success',
            'qty' => $count,
            'path' => $config['feed_locations']['path'],
            'url' => $config['feed_locations']['url']
        ];
    }
}
