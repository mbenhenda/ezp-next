<?php
/**
 * File containing the Mapper class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Content;
use ezp\Persistence\Content,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Content\Version,
    ezp\Persistence\Content\RestrictedVersion,
    ezp\Persistence\Storage\Legacy\Content\Location\Mapper as LocationMapper,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry;

/**
 *
 */
class Mapper
{
    /**
     * FieldValue converter registry
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
     */
    protected $converterRegistry;

    /**
     * Location mapper
     *
     * @var \ezp\Persistence\Storage\Persistence\Converter\Location\Mapper
     */
    protected $locationMapper;

    /**
     * Creates a new mapper.
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry $converterRegistry
     */
    public function __construct( LocationMapper $locationMapper, Registry $converterRegistry )
    {
        $this->converterRegistry = $converterRegistry;
        $this->locationMapper = $locationMapper;
    }

    /**
     * Creates a Content from the given $struct
     *
     * @param \ezp\Persistence\Content\CreateStruct $struct
     * @return Content
     */
    public function createContentFromCreateStruct( CreateStruct $struct )
    {
        $content = new Content();

        $content->name = $struct->name;
        $content->typeId = $struct->typeId;
        $content->sectionId = $struct->sectionId;
        $content->ownerId = $struct->ownerId;

        return $content;
    }

    /**
     * Creates a Location\CreateStruct for the given $content
     *
     * @param \ezp\Persistence\Content $content
     * @return Content\Location\CreateStruct
     */
    public function createLocationCreateStruct( Content $content )
    {
        $location = new Content\Location\CreateStruct();

        $location->remoteId = md5( uniqid() );
        $location->contentId = $content->id;
        $location->contentVersion = $content->version->id;

        return $location;
    }

    /**
     * Creates a new version for the given $content
     *
     * @param Content $content
     * @param int $versionNo
     * @return Version
     * @todo: created, modified, initial_language_id, status, user_id?
     */
    public function createVersionForContent( Content $content, $versionNo )
    {
        $version = new Version();

        $version->versionNo = $versionNo;
        $version->created = time();
        $version->modified = $version->created;
        $version->creatorId = $content->ownerId;
        // @todo: Is draft version correct?
        $version->status = 0;
        $version->contentId = $content->id;

        return $version;
    }

    /**
     * Converts value of $field to storage value
     *
     * @param Field $field
     * @return StorageFieldValue
     */
    public function convertToStorageValue( Field $field )
    {
        $converter = $this->converterRegistry->getConverter(
            $field->type
        );
        $storageValue = new StorageFieldValue();
        $converter->toStorageValue(
            $field->value,
            $storageValue
        );
        return $storageValue;
    }

    /**
     * Extracts Content objects (and nested) from database result $rows
     *
     * Expects database rows to be indexed by keys of the format
     *
     *      "$tableName_$columnName"
     *
     * @param array $rows
     * @return array(Content)
     */
    public function extractContentFromRows( array $rows )
    {
        $contentObjs = array();
        $versions    = array();
        $locations   = array();

        foreach ( $rows as $row )
        {
            $contentId = (int)$row['ezcontentobject_id'];
            if ( !isset( $contentObjs[$contentId] ) )
            {
                $contentObjs[$contentId] = $this->extractContentFromRow( $row );
            }
            if ( !isset( $versions[$contentId] ) )
            {
                $versions[$contentId] = array();
            }
            if ( !isset( $locations[$contentId] ) )
            {
                $locations[$contentId] = array();
            }

            $versionId = (int)$row['ezcontentobject_version_id'];
            if ( !isset( $versions[$contentId][$versionId] ) )
            {
                $versions[$contentId][$versionId]
                    = $this->extractVersionFromRow( $row );
            }
            if ( !isset( $locations[$contentId][$versionId] ) )
            {
                $locations[$contentId][$versionId] = array();
            }

            $field = (int)$row['ezcontentobject_attribute_id'];
            if ( !isset( $versions[$contentId][$versionId]->fields[$field] ) )
            {
                $versions[$contentId][$versionId]->fields[$field]
                    = $this->extractFieldFromRow( $row );
            }

            $locationId = (int)$row['ezcontentobject_tree_node_id'];
            if ( !isset( $locations[$contentId][$versionId][$locationId] ) )
            {
                $locations[$contentId][$versionId][$locationId] =
                    $this->locationMapper->createLocationFromRow(
                        $row, 'ezcontentobject_tree_'
                    );
            }
        }

        $results = array();
        foreach ( $contentObjs as $contentId => $content )
        {
            foreach ( $versions[$contentId] as $versionId => $version )
            {
                $version->fields = array_values( $version->fields );

                $newContent = clone $content;
                $newContent->version = $version;
                $newContent->locations = array_values(
                    $locations[$contentId][$versionId]
                );
                $results[] = $newContent;
            }
        }
        return $results;
    }

    /**
     * Extracts a Content object from $row
     *
     * @param array $row
     * @return Content
     */
    protected function extractContentFromRow( array $row )
    {
        $content = new Content();

        $content->id = (int)$row['ezcontentobject_id'];
        $content->name = $row['ezcontentobject_name'];
        $content->typeId = (int)$row['ezcontentobject_contentclass_id'];
        $content->sectionId = (int)$row['ezcontentobject_section_id'];
        $content->ownerId = (int)$row['ezcontentobject_owner_id'];
        $content->remoteId = $row['ezcontentobject_remote_id'];
        $content->alwaysAvailable = (bool)( $row['ezcontentobject_version_language_mask'] & 1 );
        $content->locations = array();

        return $content;
    }

    /**
     * Extracts a Version from the given $row
     *
     * @param array $row
     * @return Version
     */
    protected function extractVersionFromRow( array $row )
    {
        $version = new Version();
        $this->mapCommonVersionFields( $row, $version );
        $version->fields = array();

        return $version;
    }

    /**
     * Maps fields from $row to $version
     *
     * @param array $row
     * @param Version|RestrictedVersion $version
     * @return void
     */
    protected function mapCommonVersionFields( array $row, $version )
    {
        $version->id = (int)$row['ezcontentobject_version_id'];
        $version->versionNo = (int)$row['ezcontentobject_version_version'];
        $version->modified = (int)$row['ezcontentobject_version_modified'];
        $version->creatorId = (int)$row['ezcontentobject_version_creator_id'];
        $version->created = (int)$row['ezcontentobject_version_created'];
        $version->status = (int)$row['ezcontentobject_version_status'];
        $version->contentId = (int)$row['ezcontentobject_version_contentobject_id'];
    }

    /**
     * Extracts a Field from $row
     *
     * @param array $row
     * @return Field
     */
    protected function extractFieldFromRow( array $row )
    {
        $field = new Field();

        $field->id = (int)$row['ezcontentobject_attribute_id'];
        $field->fieldDefinitionId = (int)$row['ezcontentobject_attribute_contentclassattribute_id'];
        $field->type = $row['ezcontentobject_attribute_data_type_string'];
        $field->value = $this->extractFieldValueFromRow( $row, $field->type );
        $field->language = $row['ezcontentobject_attribute_language_code'];
        $field->versionNo = (int)$row['ezcontentobject_attribute_version'];

        return $field;
    }

    /**
     * Extracts a FieldValue of $type from $row
     *
     * @param array $row
     * @param string $type
     * @return FieldValue
     * @throws ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Exception\NotFound
     *         if the necessary converter for $type could not be found.
     */
    protected function extractFieldValueFromRow( array $row, $type )
    {
        $storageValue = new StorageFieldValue();

        $storageValue->dataFloat = (float)$row['ezcontentobject_attribute_data_float'];
        $storageValue->dataInt = (int)$row['ezcontentobject_attribute_data_int'];
        $storageValue->dataText = $row['ezcontentobject_attribute_data_text'];
        $storageValue->sortKeyInt = (int)$row['ezcontentobject_attribute_sort_key_int'];
        $storageValue->sortKeyString = $row['ezcontentobject_attribute_sort_key_string'];

        $fieldValue = new FieldValue();

        $converter = $this->converterRegistry->getConverter( $type );
        $converter->toFieldValue( $storageValue, $fieldValue );

        return $fieldValue;
    }

    /**
     * Extracts a list of RestrictedVersion objects from $rows
     *
     * @param string[][] $rows
     * @return RestrictedVersion[]
     * @todo This method works on language codes for now.
     */
    public function extractVersionListFromRows( array $rows )
    {
        $versionList = array();
        foreach ( $rows as $row )
        {
            $versionId = (int)$row['ezcontentobject_version_id'];
            if ( !isset( $versionList[$versionId] ) )
            {
                $version = new RestrictedVersion();
                $this->mapCommonVersionFields( $row, $version );
                $version->languageIds = array();

                $versionList[$versionId] = $version;
            }

            if (
                !in_array(
                    $row['ezcontentobject_attribute_language_code'],
                    $versionList[$versionId]->languageIds
                )
            )
            {
                $versionList[$versionId]->languageIds[] =
                    $row['ezcontentobject_attribute_language_code'];
            }
        }
        return array_values( $versionList );
    }
}
