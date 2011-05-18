# PHP i18n
This is a simple i18n class for PHP. Nothing fancy, but fast, because it uses caching and it is easy to use. Try it out!

Some of its features:

* Translations in ini-files
* File caching
* Simple API (`L::category_stringname`)
* Automatic finding out what language to use
* Simplicity ;)

## Requirements

* Write permissions in cache directory
* PHP 5.2 (only tested with this version, it maybe runs with other versions too)
* PHP SPL extension (installed by default)

## Setup
To use the i18n class, look at the example.php. You will find there a simple tutorial for this class in the file. Otherwise follow this easy five steps:

### 1. Create language files
To use this class, you have to use ini files for the translated strings. This could look like this:

`lang_en.ini` (English)

```ini
greeting = "Hello World!"

[category]
somethingother = "Something other..."
```

`lang_de.ini` (German)

```ini
greeting = "Hallo Welt!"

[category]
somethingother = "Etwas anderes..."
```

Save both files in the directory you will set in step 4. 
The files must be named like the filePath setting, where '{LANGUAGE}' will be replaced by the chosen language, e.g. 'en' or 'de'.

### 2. Include the class
```php
<?php
	require_once 'lib/i18n.class.php';
?>
```

### 3. Initialize the class
```php
<?php
	$i18n = new i18n();
?>
```

### 4. Set some settings if necessary
The possible settings are:

1. Language file path (the ini files) (default: `./lang/lang_{LANGUAGE}.ini`)
2. Cache file path (default: `./langcache/`)
3. The fallback language, if no one of the user languages is available (default: `en`)
4. A forced language, if you want to force a language (default: none)
5. The ini section seperator: this is used to seperate the sections in the language class. If you set the seperator to `_abc_` you could access your localized strings via `L::category_abc_stringname` if you use categories in your ini. (default: `_`)

```php
<?php
	$i18n->setCachePath('./tmp/cache');
	$i18n->setFilePath('./langfiles'); // language file path (the ini files)
	$i18n->setFallbackLang('en');
	$i18n->setForcedLang('en') // force english, even if another user language is available
	$i18n->setSectionSeperator('_');
?>
```

#### Shorthand
There is also a shorthand for that: you can set all settings in the constructor!

```php
<?php
	$i18n = new i18n('lang/lang_{LANGUAGE}.ini', 'langcache/', 'en');
?>
```

The parameters are:

1. the language file path (the ini files)
2. the language cache path
3. fallback language
4. forced language
5. ini section seperator

... exactly in this order (all parameters are optional).

### 5. Call the `init()` method to load all files and translations
Call the `init()` file to instruct the class to load the needed language file, to load the cache file or generate it  if it is not available and make the `L` class available so you can access your localizations.

```php
<?php
	$i18n->init();
?>
```

### 6. Use the localizations
To call your localizations, simple use the `L` class and a class constant for the string.

In this example, we use the translation string seen in step 1.

```php
<?php
	echo L::greeting;
	// If 'en' is applied: 'Hello World'

	echo L::category_somethingother;
	// If 'en' is applied: 'Something other...'
?>
```

Thats it!

## How it finds out the user language
This class tries to find out the user language by generating a queue of the following things:

1. Forced language (if set)
2. GET parameter 'lang' (`$_GET['lang']`)
3. SESSION parameter 'lang' (`$_SESSION['lang']`)
4. HTTP_ACCEPT_LANGUAGE (can be multiple languages) (`$_SERVER['HTTP_ACCEPT_LANGUAGE']`)
5. Fallback language

First it will remove duplicate elements and then it searches for the language files. For example, if you set the GET parameter 'lang' to 'en' without a forced language set, the class would try to find the file `lang/lang_en.ini` (if the langFilePath was set to default (`lang/lang_{LANGUAGE}.ini`)).
If this file was not there, it would try to find the language file for the language defined in the session and so on.

### How to change that
You can change this 'algorithm' by extending the i18n class. You could do it like that:

```php
<?php	
	require_once 'lib/i18n.class.php';
	class My_i18n extends i18n {
		
		public function getUserLangs() {
			$userLangs = new array();

			array_push($userLangs, $_GET['language']);

			array_push($userLangs, $_SESSION['userlanguage']);

			return $userLangs;
		}

	}

	$i18n = new My_i18n();
	// [...]
?>
```

This very basic extension of the i18n class replaces the default implementation of the `getUserLangs()`-method and only uses the GET parameter 'language' and the session parameter 'userlanguage'.
You see that this method must return an array.

Note that this is script insecure. Dont use it in a production environment! You should escape the GET parameter and so on.

## Fork it!
Please fork this project and help me with the development.