<?php
/**
 * Compat Checker
 *
 * Plugin Name: Compat Checker
 * Plugin URI:  https://seothemes.com/
 * Description: Checks that plugin meets compatibility requirements.
 * Author:      Compat Checker
 * Author URI:  https://seothemes.com/
 * Version:     1.0.0
 * GitHub URI:  seothemes/compat-checker
 * Text Domain: compat-checker
 * Domain Path: /languages
 * License:     GPL-3.0-or-later
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 *
 * @package   SeoThemes\CompatChecker
 * @link      https://seothemes.com
 * @author    Compat Checker
 * @copyright Copyright © 2018 Compat Checker
 * @license   GPL-3.0-or-later
 */

/*
|--------------------------------------------------------------------------
| Load compat checker class.
|--------------------------------------------------------------------------
|
| The Compat_Checker class must be included in your plugin before the
| class can be used. This path will be different in your project.
| You can also use the Composer autoloader if using Composer.
|
*/

require_once __DIR__ . '/src/class-compat-checker.php';

/*
|--------------------------------------------------------------------------
| Override the default settings.
|--------------------------------------------------------------------------
|
| An array of settings can be assigned to a variable and passed to the
| Compat_Checker `run()` method. They can also be added via your
| composer.json file in the extra -> compat-checker config.
|
*/

$compat_settings = apply_filters( 'compat_checker_settings', array(
	'plugin_slug'         => 'compat-checker/example-plugin.php',
	'plugin_name'         => 'Compat Checker',
	'min_php_version'     => '5.6.0',
	'min_wp_version'      => '5.0.0',
	'min_genesis_version' => '2.8.0',
	'require_genesis'     => true,
	'require_child_theme' => true,
) );

/*
|--------------------------------------------------------------------------
| Instantiate class.
|--------------------------------------------------------------------------
|
| Here we instantiate the Compat_Checker class and call the `run()` method.
| The `run` method accepts an array of settings. This can also be a path
| to your project's composer.json file, as shown in the example below:
|
| $compat_checker->run( __DIR__ . '/composer.json' );
|
*/

$compat_checker = new Compat_Checker( $compat_settings );
$compat_checker->run();

/*
|--------------------------------------------------------------------------
| Check for compatibility.
|--------------------------------------------------------------------------
|
| Check that the site meets the minimum requirements for the plugin before
| proceeding if this is a plugin for public release. If building for a
| client that meets these requirements, this code is unnecessary.
|
*/

if ( ! $compat_checker->is_compatible() ) {
	return;
}
