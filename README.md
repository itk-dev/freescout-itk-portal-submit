# Portal submit form changes for Freescout

## About
This module denies access to End-user portal submit form for anonymous users.

## Installation
- Clone this repo into the Modules directory of Freescout and name it
ItkPortalSubmit.
```
git clone git@github.com:itk-dev/freescout-itk-portal-submit.git ItkPortalSubmit
```

### Finish

Dump autoload files and empty freescout cache.
```
idc exec phpfpm composer dumpautoload
idc exec phpfpm php artisan freescout:clear-cache
```

### Enable module
Log into freescout and go to: ```/modules/list``` to enable this module.