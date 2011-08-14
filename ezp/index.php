<?php
/**
 * ez Publish - Public API Prototype
 */
namespace ezp\Content;
use ezp\Base\Configuration,
    ezp\Base\Autoloader,
    ezp\Base\Locale,
    ezp\Content,
    ezp\Content\Type\FieldDefinition;

chdir( '../' );
require 'config.php';
require 'ezp/Base/Autoloader.php';
spl_autoload_register( array( new Autoloader( $settings['base']['autoload'] ), 'load' ) );

$paths = array();
//@todo Take from configuration
foreach ( glob( '{ezp,ezx}/*', GLOB_BRACE | GLOB_ONLYDIR ) as $path )
{
    $paths[] = "{$path}/settings/";
}

Configuration::setGlobalConfigurationData( $settings );
Configuration::setGlobalDirs( $paths, 'modules' );

// Create Type manually for test
$contentType = new Type();
$contentType->identifier = 'article';

// Add some fields
$fields = array(
    'title' => array( 'ezstring', 'New Article' ),
    'tags' => array( 'ezkeyword', '' )
);
foreach ( $fields as $identifier => $data )
{
    $field = new FieldDefinition( $contentType, $data[0] );
    $field->identifier = $identifier;
    $field->defaultValue = $data[1];
    $contentType->fields[] = $field;
}

// Create section
$section = new Section();
$section->identifier = 'standard';
$section->name = "Standard";

// Create Content object
$content = new Content( $contentType, new Locale( 'eng-GB' ) );
$content->ownerId = 10;
$content->section = $section;

$content->fields['tags'] = 'ezpublish, demo, public, api';
//$content->fields['title'] = 'My new Article';
// shortcut for: $content->fields['title']->value = 'My new Article';

$content->notify( 'store' );// Needed to make sure changes in fieldtypes trickle down to field

echo "Content id: {$content->id}<br />";

echo "Fields:<br />";
foreach ( $content->fields as $identifier => $field )
{
    echo "$identifier: {$field->value}<br />";
}

