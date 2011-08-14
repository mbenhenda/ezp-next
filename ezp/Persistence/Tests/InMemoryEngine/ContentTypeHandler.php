<?php
/**
 * File containing the ContentHandler implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Tests\InMemoryEngine;
use ezp\Persistence\Content\Type\Handler as ContentTypeHandlerInterface,
    ezp\Persistence\Content\Type,
    ezp\Persistence\Content\Type\CreateStruct,
    ezp\Persistence\Content\Type\UpdateStruct,
    ezp\Persistence\Content\Type\FieldDefinition,
    ezp\Persistence\Content\Type\Group\CreateStruct as GroupCreateStruct,
    ezp\Persistence\Content\Type\Group\UpdateStruct as GroupUpdateStruct,
    ezp\Persistence\Content\Type\Group,
    ezp\Base\Exception\NotFound,
    RuntimeException;

/**
 * @see ezp\Persistence\Content\Type\Handler
 *
 * @version //autogentag//
 */
class ContentTypeHandler implements ContentTypeHandlerInterface
{
    /**
     * @var RepositoryHandler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to RepositoryHandler object that created it.
     *
     * @param RepositoryHandler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( RepositoryHandler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function createGroup( GroupCreateStruct $group )
    {
        $groupArr = (array) $group;
        return $this->backend->create( 'Content\\Type\\Group', $groupArr );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function updateGroup( GroupUpdateStruct $group )
    {
        $groupArr = (array) $group;
        $this->backend->update( 'Content\\Type\\Group', $groupArr['id'], $groupArr );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function deleteGroup( $groupId )
    {
        $this->backend->delete( 'Content\\Type\\Group', $groupId );

        // Remove group id from content types
        $types = $this->backend->find( 'Content\\Type', array( 'groupIds' => $groupId ) );
        foreach ( $types as $type )
        {
            $update = false;
            foreach ( $type->groupIds as $key => $contentTypeGroupId )
            {
                if ( $contentTypeGroupId == $groupId )
                {
                    unset( $type->groupIds[$key] );
                    $update = true;
                }
            }

            if ( $update )
            {
                // @todo If groupIds is empty, content type and content of that type should be deleted
                $this->backend->update( 'Content\\Type',
                                        $type->id,
                                        array( 'groupIds' => $type->groupIds ) );
            }
        }
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function loadAllGroups()
    {
        return $this->backend->find( 'Content\\Type\\Group', array() );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function loadGroup( $groupId )
    {
        return $this->backend->load( 'Content\\Type\\Group', $groupId );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function loadContentTypes( $groupId, $version = 0 )
    {
        return $this->backend->find(
            'Content\\Type',
            array( 'groupIds' => $groupId, 'version' => $version ),
            array( 'fieldDefinitions' => array(
                'type' => 'Content\\Type\\FieldDefinition',
                'match' => array( '_typeId' => 'id',  '_version' => 'version' ) )
            )
        );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function load( $contentTypeId, $version = 0 )
    {
        $type = $this->backend->find(
            'Content\\Type',
            array( 'id' => $contentTypeId, 'version' => $version ),
            array( 'fieldDefinitions' => array(
                'type' => 'Content\\Type\\FieldDefinition',
                'match' => array( '_typeId' => 'id',  '_version' => 'version' ) )
            )
        );

        if ( !$type )
            return null;

        return $type[0];
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function create( CreateStruct $contentType )
    {
        $contentTypeArr = (array) $contentType;
        unset( $contentTypeArr['fieldDefinitions'] );
        $contentTypeObj = $this->backend->create( 'Content\\Type', $contentTypeArr );
        foreach ( $contentType->fieldDefinitions as $field )
        {
            $contentTypeObj->fieldDefinitions[] = $this->backend->create(
                'Content\\Type\\FieldDefinition',
                array( '_typeId' => $contentTypeObj->id, '_version' => $contentTypeObj->version ) + (array) $field
            );
        }
        return $contentTypeObj;
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function update( $typeId, $version, UpdateStruct $contentType )
    {
        $contentTypeArr = (array) $contentType;
        $this->backend->updateByMatch( 'Content\\Type',
                                       array( 'id' => $typeId, 'version' => $version ),
                                       $contentTypeArr );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function delete( $contentTypeId, $version )
    {
        $this->backend->deleteByMatch( 'Content\\Type',
                                       array( 'id' => $contentTypeId, 'version' => $version ) );
        $this->backend->deleteByMatch( 'Content\\Type\\FieldDefinition',
                                           array( '_typeId' => $contentTypeId, '_version' => $version ) );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function createVersion( $userId, $contentTypeId, $fromVersion, $toVersion )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function copy( $userId, $contentTypeId, $version )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function unlink( $groupId, $contentTypeId, $version )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function link( $groupId, $contentTypeId, $version )
    {
        $list = $this->backend->find('Content\\Type', array( 'id' => $contentTypeId, 'version' => $version ) );
        if ( !isset( $list[0] ) )
            throw new NotFound( 'Content\\Type', "{$contentTypeId}' and version '{$version}" );

        if ( !$this->backend->load('Content\\Type\\Group', $groupId ) )
            throw new NotFound( 'Content\\Type\\Group', $groupId );

        $this->backend->updateByMatch( 'Content\\Type',
                               array( 'id' => $contentTypeId, 'version' => $version ),
                               array( 'groupIds' => array_merge( $list[0]->groupIds, array( $groupId ) ) ) );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function addFieldDefinition( $contentTypeId, $version, FieldDefinition $fieldDefinition )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function removeFieldDefinition( $contentTypeId, $version, $fieldDefinitionId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function updateFieldDefinition( $contentTypeId, $version, FieldDefinition $fieldDefinition )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }

    /**
     * @see ezp\Persistence\Content\Type\Handler
     */
    public function updateContentObjects( $contentTypeId, $version, $fieldDefinitionId )
    {
        throw new RuntimeException( '@TODO: Implement' );
    }
}
?>
