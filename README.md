
Composer plugin that scaffolds the custom code directories and settings for a Drupal project. The main idea is to keep all custom code outside the web root. 

When running Composer install or update, the following directories and files will be created, if they don't exist:

```
├─ files
│  ├─ private
│  └─ public
...
├─ src
│  └─ Drupal
│     ├─ Modules
│     ├─ Profiles
│     ├─ Settings
│     │  ├─ settings.php
│     │  └─ settings.local.php (not under VCS control)
│     └─ Themes
```

Under web root symlinks are created in the proper location. For instance `src/Drupal/Modules` is symlinked as `web/modules/custom`, `files/public` is symlinked as `web/sites/default/files` and so on.
