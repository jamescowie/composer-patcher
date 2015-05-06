# Patcher
Applying generic patches using the `patch` tool using Composer's `script` feature.  
The patching is idempotent as much as the `patch` tool is, meaning patches will _not_ be applied if `patch` decides not to.

## Project setup

a) Patches need to be declared in the `extra` config area of Composer (root package only):
```json
  "extra": {
    "patches": {
        "magento": {
          "autoloader-patch": {
              "name": "Autoloader-patcher",
              "title": "Allow composer autoloader to be applied to Mage.php",
              "url": "https://raw.githubusercontent.com/inviqa/magento-patches/magento-composer-autoloader-patch/composer-autoloader/0001-Adding-Composer-autoloader-to-Mage.patch?token=token"
          }
        }
      }
  }
```

b) Additional scripts need to be added for automatic patching on `install` or `update` (root package only):
```json
  "scripts": {
    "post-install-cmd": [
        "php bin/autoloader-patcher"
    ],
    "post-update-cmd": [
        "php bin/autoloader-patcher"
    ]
  }
```
Theoretically, you can use whatever [Composer event](https://getcomposer.org/doc/articles/scripts.md#event-names) you want, 
or even [trigger the events manually](https://getcomposer.org/doc/articles/scripts.md#running-scripts-manually).

c) explicitly setting the bin dir at the root level (root package only):
```json
  "config": {
    "bin-dir": "bin"
  }
```

d) the `patch` tool must be available
