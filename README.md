# Patcher
Applying generic patches using the `patch` tool using Composer's `script` feature.  
The patching is idempotent as much as the `patch` tool is, meaning patches will _not_ be re-applied if `patch` decides not to.

## Project setup

a) Patches need to be declared in the `extra` config area of Composer (root package only):
```json
    "extra": {
        "patches": {
            "patch-group-1": {
                "patch-name-1": {
                    "title": "Allow composer autoloader to be applied to Mage.php",
                    "url": "https://url/to/file1.patch"
                }
            },
            "patch-group-2": {
                "patch-name-1": {
                    "title": "Fixes Windows 8.1",
                    "url": "https://url/to/file2.patch"
                }
            }        
        }
    }
```
A patch's _group_ and _name_ will create its ID, used internally (i.e. `patch-group-1/patch-name-1`), so make sure you follow these 2 rules:
- `patch-group-1` MUST be unique in the `patches` object literal
- `patch-name-1` MUST be unique in its patch _group_

Examples of patch groups: "magento", "drupal", "security".
Examples of patch names: "CVS-1", "composer-autoloader".

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
