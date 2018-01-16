# Subscription checker

Client side module that is used to activate and check subscription status.

## Installation

 1. Open `<magento_root>/composer.json` and change `minimum-stability` setting to `dev`.
 2. Run the following commands:

    ```bash
    cd <magento_root>
    composer config repositories.subscription-checker vcs git@github.com:swissup/subscription-checker.git
    composer require swissup/subscription-checker:dev-master --prefer-source
    bin/magento module:enable Swissup_Core Swissup_SubscriptionChecker
    bin/magento setup:upgrade
    ```
