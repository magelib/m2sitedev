<?php
namespace Gene\ApplePay\Observer;

use Gene\ApplePay\Block\Shortcut\Button;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AddShortcuts
 */
class AddShortcuts implements ObserverInterface
{
    /**
     * Add apple pay shortcut button
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Remove button from catalog pages
        if ($observer->getData('is_catalog_product')) {
            return;
        }

        /** @var ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();
        $shortcut = $shortcutButtons->getLayout()->createBlock(Button::class);
        $shortcutButtons->addShortcut($shortcut);
    }
}
