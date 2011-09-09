<?php
/**
 * File containing the ezp\Content class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp;
use ezp\Base\Model,
    ezp\Base\ModelDefinition,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Content\Translation,
    ezp\Content\Type,
    ezp\Content\Location,
    ezp\Content\Section,
    ezp\Base\Proxy,
    ezp\Content\Version,
    ezp\Content\Version\StaticCollection as VersionCollection,
    ezp\Persistence\Content as ContentValue,
    DateTime,
    InvalidArgumentException;

/**
 * This class represents a Content item
 *
 * It is used for both input and output manipulation.
 *
 * @property-read mixed $id The Content's ID, automatically assigned by the persistence layer
 * @property-read int $currentVersionNo The Content's current version
 * @property-read int $status The Content's status, as one of the ezp\Content::STATUS_* constants
 * @property string[] $name The Content's name
 * @property-read mixed $ownerId Id of the user object that owns the content
 * @property-read bool $alwaysAvailable The Content's always available flag
 * @property-read string $remoteId The Content's remote identifier (custom identifier for the object)
 * @property-read mixed $sectionId Read property for section id, use with object $section to change
 * @property-read mixed $typeId Read property for type id
 * @property-read \ezp\Content\Type $contentType The Content's type
 * @property-read \ezp\Content\Version[] $versions
 *                Iterable collection of versions for content. Array-accessible :;
 *                <code>
 *                $myFirstVersion = $content->versions[1];
 *                $myThirdVersion = $content->versions[3];
 *                </code>
 * @property-read \ezp\Content\Version $currentVersion Current version of content
 * @property-read \ezp\Content\Location $mainLocation
 * @property-read \ezp\Content\Location[] $locations
 *                Locations for content. Iterable, countable and Array-accessible (with numeric indexes)
 *                First location referenced in the collection represents the main location for content
 *                <code>
 *                $mainLocation = $content->locations[0];
 *                $anotherLocation = $content->locations[2];
 *                $locationById = $content->locations->byId( 60 );
 *                </code>
 * @property-read DateTime $creationDate The date the object was created
 * @property \ezp\Content\Section $section The Section the content belongs to
 * @property \ezp\Content\Relation[] $relations Collection of \ezp\Content\Relation objects, related to the current one
 * @property \ezp\Content\Relation[] $reverseRelations Collection of \ezp\Content\Relation objects, reverse-related to the current one
 * @property \ezp\Content\Translation[] $translations
 *           Collection of content's translations, indexed by locale (ie. eng-GB)
 *           <code>
 *           $myEnglishTranslation = $content->translations["eng-GB"];
 *           $myEnglishTitle = $content->translations["eng-GB"]->fields->title; // Where "title" is the field identifier
 *           </code>
 * @property \ezp\Content\Field[] $fields
 *           Collection of content's fields in default (current) language.
 *           Shorthand property to directly access to the content's fields in current language
 *           <code>
 *           $myTitle = $content->fields->title; // Where "title" is the field identifier
 *           </code>
 * @property int $ownerId Owner identifier
 */
class Content extends Model implements ModelDefinition
{
    /**
     * Publication status constants
     * @var int
     */
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;

    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'currentVersionNo' => false,
        'status' => false,
        'name' => true, // @todo: Make readOnly and generate on store event from attributes based on type nameScheme
        'ownerId' => true,// @todo make read only by providing interface that takes User as input
        'alwaysAvailable' => true,
        'remoteId' => true,// @todo Make readonly and deal with this internally (in all DO's)
        'sectionId' => false,
        'typeId' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'creationDate' => false,
        'mainLocation' => false,
        'section' => false,
        'owner' => false,
        'fields' => true,
        'contentType' => false,
        'versions' => false,
        'locations' => true,
        //'translations' => true,
        'relations' => false,
        'reversedRelations' => false,
        'currentVersion' => false
    );

    /**
     * The Section the content belongs to
     *
     * @var \ezp\Content\Section
     */
    protected $section;

    /**
     * Locations collection
     *
     * @var \ezp\Content\Location[]
     */
    protected $locations;

    /**
     * Content type object that this Content object is an instance of
     *
     * @var \ezp\Content\Type
     */
    protected $contentType;

    /**
     * Relations collection
     *
     * @var \ezp\Content\Relation[]
     */
    protected $relations;

    /**
     * Reverse relation collection
     *
     * @var \ezp\Content\Relation[]
     */
    protected $reversedRelations;

    /**
     * Versions
     *
     * @var \ezp\Content\Version[]
     */
    protected $versions;

    /**
     * Owner ( User )
     *
     * @var \ezp\User
     */
    protected $owner;

    /**
     * Create content based on content type object
     *
     * @param \ezp\Content\Type $contentType
     * @param \ezp\User $owner
     */
    public function __construct( Type $contentType, User $owner )
    {
        $this->properties = new ContentValue( array(
            'typeId' => $contentType->id,
            'status' => self::STATUS_DRAFT,
            'ownerId' => $owner->id
        ) );
        /*
        @TODO Make sure all dynamic properties writes to value object if scalar value (creationDate (int)-> properties->created )
        */
        $this->contentType = $contentType;
        $this->owner = $owner;
        $this->locations = new TypeCollection( 'ezp\\Content\\Location' );
        $this->relations = new TypeCollection( 'ezp\\Content\\Relation' );
        $this->reversedRelations = new TypeCollection( 'ezp\\Content\\Relation' );
        $this->versions = new VersionCollection( array( new Version( $this ) ) );
    }

    /**
     * Returns definition of the content object, atm: permissions
     *
     * @access private
     * @return array
     */
    public static function definition()
    {
        $def = array(
            'module' => 'content',
            'functions' => array(
                // Note: Functions skipped in api: bookmark, dashboard, tipafriend and pdf
                // @todo Add StateLimitations on functions that need them when object states exists in public api
                'create' => array(
                    // Note: Limitations 'Class' & 'Section' is copied from 'read' function further bellow
                    'ParentOwner' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            return $parent && in_array( $parent->content->ownerId, $limitationsValues, true );
                        },
                    ),
                    'ParentGroup' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            if ( !$parent )
                                return false;

                            foreach ( $parent->content->owner->getGroups() as $group )
                            {
                                if ( in_array( $group->id, $limitationsValues, true ) )
                                    return true;
                            }

                            return false;
                        },
                    ),
                    'ParentClass' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            return $parent && in_array( $parent->content->typeId, $limitationsValues, true );
                        },
                    ),
                    'ParentDepth' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            return $parent && in_array( $parent->depth, $limitationsValues, true );
                        },
                    ),
                    'Node' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            return $parent && in_array( $parent->id, $limitationsValues, true );
                        },
                    ),
                    'Subtree' => array(
                        'compare' => function( Content $content, array $limitationsValues, Repository $repository, Location $parent = null )
                        {
                            if ( !$parent )
                                return false;

                            foreach ( $limitationsValues as $limitationPathString )
                            {
                                if ( strpos( $parent->pathString, $limitationPathString ) === 0 )
                                    return true;
                            }

                            return false;
                        },
                    ),
                    'Language' => array(
                        'compare' => function( Content $content, array $limitationsValues )
                        {
                            // Note: Copied to other functions further down
                            // @todo: $limitationsValues is a list of languageCodes, so it needs to be matched against
                            //        language of content somehow when that api is in place
                            return false;
                        },
                    ),
                ),
                'read' => array(
                    // Note: All limitations copied to other functions further bellow
                    'Class' => array(
                        'compare' => function( Content $content, array $limitationsValues )
                        {
                            return in_array( $content->typeId, $limitationsValues, true );
                        },
                    ),
                    'Section' => array(
                        'compare' => function( Content $content, array $limitationsValues )
                        {
                            return in_array( $content->sectionId, $limitationsValues, true );
                        },
                    ),
                    'Owner' => array(
                        'compare' => function( Content $content, array $limitationsValues )
                        {
                            return in_array( $content->ownerId, $limitationsValues, true );
                        },
                    ),
                    'Group' => array(
                        'compare' => function( Content $content, array $limitationsValues )
                        {
                            foreach ( $content->owner->getGroups() as $group )
                            {
                                if ( in_array( $group->id, $limitationsValues, true ) )
                                    return true;
                            }

                            return false;
                        },
                    ),
                    'Node' => array(
                        'compare' => function( Content $content, array $limitationsValues )
                        {
                            foreach ( $content->locations as $location )
                            {
                                if ( in_array( $location->id, $limitationsValues, true ) )
                                    return true;
                            }

                            return false;
                        },
                    ),
                    'Subtree' => array(
                        'compare' => function( Content $content, array $limitationsValues )
                        {
                            foreach ( $content->locations as $location )
                            {
                                foreach ( $limitationsValues as $limitationPathString )
                                {
                                    if ( strpos( $location->pathString, $limitationPathString ) === 0 )
                                        return true;
                                }
                            }

                            return false;
                        },
                    ),
                ),
                'edit' => array(
                    // Note: Limitations copied over from 'read' + 'Language' from 'create'
                ),
                'remove' => array(
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                ),
                'move' => array(),
                'versionread' => array(
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                ),
                'versionremove' => array(
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                ),
                'view_embed' => array(
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                ),
                'diff' => array(
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                ),
                'reverserelatedlist' => array(),
                'translate' => array(
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                    // 'Language' is copied from 'create'
                ),
                'urltranslator' => array(),
                'pendinglist' => array(),
                'manage_locations' => array(
                    // Note: Limitations copied over from 'read', getting 'Group' as a bonus further down
                ),
                'hide' => array(
                    // Note: Limitations copied over from 'read' further down
                    // 'Language' is copied from 'create'
                ),
                'restore' => array(),
                'cleantrash' => array(),
            ),
        );

        //// Limitations are copied to reduce duplication (never copied to 'read' as it requires 'query' support)

        // Create: Copy 'Class' & 'Section' from 'read'
        $def['functions']['create']['Class'] = $def['functions']['read']['Class'];
        $def['functions']['create']['Class'] = $def['functions']['read']['Class'];

        // Edit: Copy 'Language' from 'creat'
        $def['functions']['edit']['Language'] = $def['functions']['create']['Language'];
        $def['functions']['translate']['Language'] = $def['functions']['create']['Language'];
        $def['functions']['hide']['Language'] = $def['functions']['create']['Language'];

        // Union duplicate code from 'read'
        $def['functions']['edit'] = $def['functions']['edit'] + $def['functions']['read'];
        $def['functions']['remove'] = $def['functions']['remove'] + $def['functions']['read'];
        $def['functions']['versionread'] = $def['functions']['versionread'] + $def['functions']['read'];
        $def['functions']['versionremove'] = $def['functions']['versionremove'] + $def['functions']['read'];
        $def['functions']['view_embed'] = $def['functions']['view_embed'] + $def['functions']['read'];
        $def['functions']['diff'] = $def['functions']['diff'] + $def['functions']['read'];
        $def['functions']['translate'] = $def['functions']['translate'] + $def['functions']['read'];
        $def['functions']['manage_locations'] = $def['functions']['manage_locations'] + $def['functions']['read'];
        $def['functions']['hide'] = $def['functions']['hide'] + $def['functions']['read'];

        return $def;
    }

    /**
     * Return Main location object on this Content object
     *
     * @return \ezp\Content\Location
     */
    protected function getMainLocation()
    {
        return $this->locations[0];
    }

    /**
     * Return a collection containing all available versions of the Content
     *
     * @return \ezp\Content\Version[]
     */
    protected function getVersions()
    {
        return $this->versions;
    }

    /**
     * Find current version amongst version objects
     *
     * @return \ezp\Content\Version|null
     */
    protected function getCurrentVersion()
    {
        foreach ( $this->versions as $contentVersion )
        {
            if ( $this->properties->currentVersionNo == $contentVersion->versionNo )
                return $contentVersion;
        }
        return null;
    }

    /**
     * Return Type object
     *
     * @return \ezp\Content\Type
     */
    protected function getContentType()
    {
        if ( $this->contentType instanceof Proxy )
        {
            return $this->contentType = $this->contentType->load();
        }
        return $this->contentType;
    }

    /**
     * Get fields of current version
     * @todo Do we really want/need this shortcut?
     *
     * @return \ezp\Content\Field[]
     */
    protected function getFields()
    {
        return $this->getCurrentVersion()->fields;
    }

    /**
     * Sets the Section the Content belongs to
     *
     * @param \ezp\Content\Section $section
     */
    protected function setSection( Section $section )
    {
        $this->section = $section;
        $this->properties->sectionId = $section->id;
    }

    /**
     * Returns the Section the Content belongs to
     *
     * @return \ezp\Content\Section
     */
    protected function getSection()
    {
        if ( $this->section instanceof Proxy )
        {
            $this->section = $this->section->load();
        }
        return $this->section;
    }

    /**
     * Returns the User the Content is owned by
     *
     * @return \ezp\User
     */
    protected function getOwner()
    {
        if ( $this->owner instanceof Proxy )
        {
            $this->owner = $this->owner->load();
        }
        return $this->owner;
    }

    /**
     * Adds a new location to content under an existing one.
     *
     * @param \ezp\Content\Location $parentLocation
     * @return \ezp\Content\Location
     */
    public function addParent( Location $parentLocation )
    {
        $newLocation = new Location( $this );
        $newLocation->parent = $parentLocation;
        return $newLocation;
    }

    /**
     * Gets locations
     *
     * @return \ezp\Content\Location[]
     */
    protected function getLocations()
    {
        return $this->locations;
    }

    /**
     * Gets Content relations
     *
     * @return \ezp\Content[]
     */
    protected function getRelations()
    {
        return $this->relations;
    }

    /**
     * Gets Content reverse relations
     *
     * @return \ezp\Content[]
     */
    protected function getReverseRelations()
    {
        return $this->reverseRelations;
    }

    /**
     * Clone content object
     */
    public function __clone()
    {
        $this->properties = clone $this->properties;
        $this->properties->id = false;
        $this->properties->status = self::STATUS_DRAFT;
        // @todo make sure everything is cloned (versions / fields...) or remove these clone functions

        // Get the location's, so that new content will be the old one's sibling
        $oldLocations = $this->locations;
        $this->locations = new TypeCollection( 'ezp\\Content\\Location' );
        foreach ( $oldLocations as $location )
        {
            $this->addParent( $location->parent );
        }
    }
}
?>
