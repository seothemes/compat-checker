# Compat Checker

Composer package or drop-in class to check PHP, WordPress and Genesis compatibility. It displays a deactivation notice if minimum requirements are not met and provides helper functions to check if a plugin is compatible.

## Installation

Compat Checker can be installed as a drop-in class or using Composer.

### Composer (recommended)

Navigate to your project root in terminal:

```shell
cd my-project
```

Require Composer package (Composer must be installed);

```shell
composer require seothemes/compat-checker
```

### Manual (drop-in)

Download the `src/class-compat-checker.php` file and place it in your project, in any directory.

## Usage

### 1. Load Class

#### Composer autoloader

Require the `vendor/autoload.php` file in your project, if you haven't already:

```php
require_once __DIR__ . '/vendor/autoload.php';
```

This automatically loads the class and makes it ready for use.

#### Manually require

Require the `src/class-compat-checker.php` file in your project:

```php
require_once __DIR__ . '/src/class-compat-checker.php';
```

*Note: This may be different depending on where you have placed the class*

### 2. Add Settings

Genesis Compat provides multiple ways to define your settings. Below are the 2 easiest methods:

#### composer.json

Add the settings array to the `extra` `compat-checker` config:

```json5
{
  "name": "company/package",
  "require": {
    "php": ">=5.6" // Used if no min_php_version defined.
  },
  "extra": {
    "compat-checker": {
      "plugin_slug": "compat-checker/example-plugin.php",
      "plugin_name": "Compat Checker",
      "min_php_version": "5.4.0",
      "min_wp_version": "5.0.0",
      "min_genesis_version": "2.8.0",
      "require_genesis": "true",
      "require_child_theme": "true"
    }
  }
}
```

#### Array

Create a PHP array and assign it to a variable:

```php
$compat_settings = apply_filters( 'compat_checker_settings', array(
	'plugin_slug'         => 'compat-checker/example-plugin.php',
	'plugin_name'         => 'Compat Checker',
	'min_php_version'     => '5.4.0',
	'min_wp_version'      => '5.0.0',
	'min_genesis_version' => '2.8.0',
	'require_genesis'     => true,
	'require_child_theme' => true,
) );
```

### 3. Instantiate Class

Genesis Compat has a `run()` method that must be used to run the class. This method accepts either a path to the `composer.json` file or an array of settings. If neither are provided, the default settings will be used, which can also be filtered using the `compat_checker_defaults` hook. 

#### Using composer.json

If using `composer.json` for your settings, pass in the path to the `run()` method:

```php
$compat_checker = new Compat_Checker();
$compat_checker->run( __DIR__ . '/composer.json' );
```

#### Using PHP array

```php
$compat_checker = new Compat_Checker();
$compat_checker->run( $compat_settings );
```

### 4. Check Compatibility

Now that the class is setup you can use it to check that your plugin meets all of the minimum requirements before proceeding. The easiest way to do this is to use the `is_compatible()` method, which can be called right after `run()`:

```php
if ( ! $compat_checker->is_compatible() ) {
	return;
}
```

This will stop your program running, exiting safely after displaying the deactivation notice.
