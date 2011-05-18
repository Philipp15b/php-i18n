# PHP i18n
This is a simple i18n class for PHP. Nothing fancy, but fast, because it uses caching and it is easy to use. Try it out!

Some of its features:

* Translations in ini-files
* Caching makes it very fast
* Simple API (`L::category_stringname`)
* Automatic finding out what language to use
* Simplicity ;)

## Requirements

* Write permissions in cache directory
* PHP 5.2 (only tested with this version, it maybe runs with other versions too)
* PHP SPL extension (installed by default)

## Setup
To use the i18n class, look at the example.php. You will find there a simple tutorial for this class in the file. Otherwise follow this easy five steps:

### 1. Include the class
```php
require_once 'lib/simple-i18n.class.php';
```

### 2. Initialize the class
```php
$i18n = new i18n();
```

### 3. Set some settings if neccessary
The possible settings are:

1. Language file path (the ini files) (default: `./lang/lang_{LANGUAGE}.ini`)
2. Cache file path (default: `./langcache/`)
3. The fallback language, if no one of the user languages is available (default: `en`)
4. A forced language, if you want to force a language (default: none)
5. The ini section seperator: this is used to seperate the sections in the language class. If you set the seperator to `_abc_` you could access your localized strings via `L::category_abc_stringname` if you use categories in your ini. (default: `_`)

```php
$i18n->setCachePath('./tmp/cache');
$i18n->setFilePath('./langfiles'); // language file path (the ini files)
$i18n->setFallbackLang('en');
$i18n->setForcedLang('en') // force english, even if another user language is available
$i18n->setSectionSeperator('_');
```

#### Shorthand
There is also a shorthand for that: you can set all settings in the constructor!
```php
$i18n = new i18n('lang/lang_{LANGUAGE}.ini', 'langcache/', 'en');
```
The parameters are:

1. the language file path (the ini files)
2. the language cache path
3. fallback language
4. forced language
5. ini section seperator

... exactly in this order (all parameters are optional).

### 4. Call the `init()` method to load all files and translations
Call the `init()` file to instruct the class to load the needed language file, to load the cache file or generate it  if it is not available and make the `L` class available so you can access your localizations.

```php
$i18n->init();
```

### 5. Use the localizations
To call your localizations, simple use the `L` class and a class constant for the string.

```php
echo L::category_stringname;
```

Thats it!

## Fork it!
Please fork this project and help me with the development.