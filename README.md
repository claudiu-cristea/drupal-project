[![ci](https://github.com/claudiu-cristea/drupal-project/actions/workflows/ci.yml/badge.svg)](https://github.com/claudiu-cristea/drupal-project/actions/workflows/ci.yml)

Allows to keep all Drupal code outside of web root.

The package Composer plugin that scaffolds the custom code directories and settings for a Drupal project. The main idea is to keep all custom code outside the web root.

When running Composer install or update, the following directories and files will be created, if they don't exist. Under web root symlinks are created in the proper location. For instance `src/Drupal/Modules` is symlinked as `web/modules/custom`, `files/public` is symlinked as `web/sites/default/files` and so on.

```
Directory/file                 Symlinked as
├─ files
│  ├─ private
│  ├─ public                   -> web/sites/default/files
│  └─ .gitignore                  Both, private and public are ignored
...
├─ src
│  └─ Drupal
│     ├─ Modules               -> web/modules/custom
│     ├─ Profiles              -> web/profiles/custom
│     ├─ Settings
│     │  ├─ settings.php       -> web/sites/default/settings.php
│     │  ├─ settings.local.php -> web/sites/default/settings.local.php
│     │  └─ .gitignore            settings.local.php is ignored
│     └─ Themes                -> web/themes/custom
```

The webroot directory (`web/`) is also added to the project's `.gitignore` if it isn't yet there.
