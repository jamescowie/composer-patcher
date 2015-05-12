# Patcher
Applying generic patches using the `patch` tool using Composer's `script` feature.  
The patching is idempotent as much as the `patch` tool is, meaning patches will _not_ be re-applied if `patch` decides not to.

## Project setup

a) Patches need to be declared in the `extra` config area of Composer (root package only):
```json
    "extra": {
        "magento-root-dir": "public",
        "patches": {
            "patch-group-1": {
                "patch-name-1": {
                    "type": "patch",
                    "title": "Allow composer autoloader to be applied to Mage.php",
                    "url": "https://url/to/file1.patch"
                }
            },
            "patch-group-2": {
                "patch-name-1": {
                    "title": "Fixes Windows 8.1",
                    "url": "https://url/to/file2.patch"
                }
            },
            "shell-patch-group-1": {
                "magento-shell-patch-name-1": {
                    "type": "shell",
                    "title": "Magento security fix",
                    "url": "https://url/to/magento/shell/patch.sh"
                }
            }
        }
    }
```

There are two types of patches:
- type **"patch"** - generic patch/diff files, applied using the patch tool;
- type **"shell"** - official Magento shell patches, which are able to apply and/or revert themselves and are self-contained.  

If no type is declared, **"patch"** is assumed. If you have such a patch type declared, you **must** set the **"magento-root-dir"**
extra config, pointing to the Mage root folder, or else it will fail with an error.

"Shell" patches will be copied in the Mage root (set by the **"magento-root-dir"** extra config), triggered, then removed.

A patch's _group_ and _name_ will create its ID, used internally (i.e. `patch-group-1/patch-name-1`), so make sure you follow these 2 rules:
- `patch-group-1` MUST be unique in the `patches` object literal
- `patch-name-1` MUST be unique in its patch _group_

Examples of patch groups: "magento", "drupal", "security".
Examples of patch names: "CVS-1", "composer-autoloader".

b) Additional scripts callbacks need to be added for automatic patching on `install` or `update` (root package only):
```json
  "scripts": {
    "post-install-cmd": "Inviqa\\Command::patch",
    "post-update-cmd": "Inviqa\\Command::patch"
  }
```
You can use whatever [Composer *Command* event](https://getcomposer.org/doc/articles/scripts.md#event-names) you want, 
or even [trigger the events manually](https://getcomposer.org/doc/articles/scripts.md#running-scripts-manually).  
Again, note that only *Command events* are supported. Please check the above link to see which ones are they.

c) the `patch` tool must be available
