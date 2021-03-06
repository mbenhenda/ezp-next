<?php
/**
 * File containing the bootstrapping of eZ Publish Next
 *
 * Returns instance of Service Container setup with configuration service and setups autoloader.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */


use eZ\Publish\Core\Base\ClassLoader,
    eZ\Publish\Core\Base\ConfigurationManager,
    eZ\Publish\Core\Base\ServiceContainer;

// Setup autoloaders
if ( !( $settings = include ( __DIR__ . '/config.php' ) ) )
{
    die( 'Could not find config.php, please copy config.php-DEVELOPMENT to config.php customize to your needs!' );
}

require __DIR__ . '/eZ/Publish/Core/Base/ClassLoader.php';
$loader = new ClassLoader( $settings['base']['ClassLoader']['Repositories'] );
if ( isset( $settings['base']['ClassLoader']['ClassMap'] ) )
    $loader->registerClassMap( $settings['base']['ClassLoader']['ClassMap'] );
spl_autoload_register( array( $loader, 'load' ) );

// Zeta Components, if using ezcBase loader
if ( isset( $settings['base']['ClassLoader']['ezcBase'] ) )
{
    require $settings['base']['ClassLoader']['ezcBase'];
    spl_autoload_register( array( 'ezcBase', 'autoload' ) );
}


$configManager = new ConfigurationManager(
    $settings,
    $settings['base']['Configuration']['Paths']
);

// Access matching before we get active modules or opposite?
// anyway access matching should use event filters hence be optional.

// Setup configuration for modules
/*$paths = array();
foreach ( $settings['base']['ClassLoader']['Repositories'] as $ns => $nsPath )
{
    foreach ( glob( "{$nsPath}/*", GLOB_ONLYDIR ) as $path )//@todo Take from configuration
    {
        $paths[] = "{$path}/settings/";
    }
}

$configManager->setGlobalDirs( $paths, 'modules' );*/

$sc = new ServiceContainer(
    $configManager->getConfiguration('service')->getAll(),
    array(
        '$classLoader' => $loader,
        '$configurationManager' => $configManager,
    )
);

return $sc;
