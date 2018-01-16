<?php
/**
 * Copyright © 2017 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\GoogleShopping\Cron;

use Magmodules\GoogleShopping\Model\Generate;
use Psr\Log\LoggerInterface;

class GenerateFeeds
{
    protected $generate;
    protected $logger;

    /**
     * GenerateFeeds constructor.
     * @param Generate $generate
     * @param LoggerInterface $logger
     */
    public function __construct(
        Generate $generate,
        LoggerInterface $logger
    ) {
        $this->generate = $generate;
        $this->logger = $logger;
    }

    /**
     * Execute: Run all Google Shopping Feed generation.
     */
    public function execute()
    {
        try {
            $this->generate->generateAll();
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
