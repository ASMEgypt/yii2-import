# yii2-import
Yii2 module for import data from files to database between activeRecord

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

### Install

Either run

```
$ php composer.phar require execut/yii2-import "dev-master"
```

or add

```
"execut/yii2-import": "dev-master"
```

to the ```require``` section of your `composer.json` file.

Add module bootstrap in frontend application config:
```php
    'bootstrap' => [
    ...
        \execut\import\bootstrap\Console::class,
    ...
    ],
```

Add module bootstrap in console application config:
```php
    'bootstrap' => [
    ...
        \execut\import\bootstrap\Console::class,
    ...
    ],
```