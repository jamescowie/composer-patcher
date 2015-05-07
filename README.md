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

b) Additional scripts callbacks need to be added for automatic patching on `install` or `update` (root package only):
```json
  "scripts": {
    "post-install-cmd": [
        "Inviqa\\Command::patch"
    ],
    "post-update-cmd": [
        "Inviqa\\Command::patch"
    ]
  }
```
You can use whatever [Composer *Command* event](https://getcomposer.org/doc/articles/scripts.md#event-names) you want, 
or even [trigger the events manually](https://getcomposer.org/doc/articles/scripts.md#running-scripts-manually).  
Again, note that only *Command events* are supported. Please check the above link to see which ones are they.

c) the `patch` tool must be available
