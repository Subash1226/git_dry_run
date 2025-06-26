# Magento 2 Admin Theme
This module is used for change admin Login page logo and admin dashboard logo.
You have to change you logo in following location. **view/adminhtml/web/images**


# Features
- Change admin login page logo
- Change admin dashboard logo.


# Installation Instruction
```
- Copy the content of the repo to the Magento 2 app/code/Niks/Admintheme
- Run command: php bin/magento setup:upgrade
- Run command: php bin/magento setup:di:compile
- Run Command: php bin/magento setup:static-content:deploy
- Now Flush Cache: php bin/magento cache:flush
```
