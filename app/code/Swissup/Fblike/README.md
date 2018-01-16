# Facebook Like Button

#### Installation

```bash
cd <magento_root>
composer config repositories.swissup/fblike vcs git@github.com:swissup/fblike.git
composer require swissup/fblike
bin/magento module:enable Swissup_Fblike
bin/magento setup:upgrade
```

###### Add Like Button To Catalog
add code to any place in catalog list.phtml
```php
    <?php echo $this->helper('Swissup\Fblike\Helper\Like')->getProductLike($_product); ?>
```
