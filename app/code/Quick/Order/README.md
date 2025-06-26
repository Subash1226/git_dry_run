# Mage2 Module Magelearn Customform

    ``magelearn/module-customform``

 - [Main Functionalities](#markdown-header-main-functionalities)
 - [Installation](#markdown-header-installation)
 - [Configuration](#markdown-header-configuration)
 - [Specifications](#markdown-header-specifications)
 - [Attributes](#markdown-header-attributes)


## Main Functionalities
Magento2 Display Custom Form on Front End and save data in admin Grid. Complete module to save image field and display in grid. Also have list and view page on frontend with pagination.
<p><img src="https://i.ibb.co/JQ6hsw1/Manage-Customform-Magento-Admin.png"></p>
<p><img src="https://i.ibb.co/C6gDgFP/Manage-Customform-Magento-Admin1.png"></p>
<p><img src="https://i.ibb.co/C1Y9jyF/12222.png"></p>
<p><img src="https://i.ibb.co/xM2VfFv/Customform-About-Customer-Inquiry.png"></p>
## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

 - Unzip the zip file in `app/code/Magelearn`
 - Enable the module by running `php bin/magento module:enable Magelearn_Customform`
 - Apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

 - Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
 - Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
 - Install the module composer by running `composer require magelearn/module-customform`
 - enable the module by running `php bin/magento module:enable Magelearn_Customform`
 - apply database updates by running `php bin/magento setup:upgrade`\*
 - Flush the cache by running `php bin/magento cache:flush`


## Configuration

- Front end URLs:
    - for customform: `/customform/`
    - for customform list page `/customform/index/list`
    - for customform view page `/customform/index/view/id/**`


## Specifications




## Attributes

