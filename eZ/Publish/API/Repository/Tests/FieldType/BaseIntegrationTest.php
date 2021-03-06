<?php
/**
 * File contains: eZ\Publish\Core\Persistence\Legacy\Tests\RepositoryTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Tests\FieldType;
use eZ\Publish\API\Repository\Tests,
    eZ\Publish\API\Repository;

/**
 * Integration test for the legacy storage
 *
 * @group integration
 */
abstract class BaseIntegrationTest extends Tests\BaseTest
{
    /**
     * Identifier of the custom field
     *
     * @var string
     */
    protected $customFieldIdentifier = "data";

    /**
     * Id of test content type
     *
     * @var string
     */
    protected static $contentTypeId;

    /**
     * Id of test content
     *
     * @var string
     */
    protected static $contentId;

    /**
     * Current version of test content
     *
     * @var string
     */
    protected static $contentVersion;

    /**
     * Get name of tested field tyoe
     *
     * @return string
     */
    abstract public function getTypeName();

    /**
     * Get field definition data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    abstract public function getFieldDefinitionData();

    /**
     * Get initial field externals data
     *
     * @return array
     */
    abstract public function getInitialFieldData();

    /**
     * Get externals field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    abstract public function getExternalsFieldData();

    /**
     * Get update field externals data
     *
     * @return array
     */
    abstract public function getUpdateFieldData();

    /**
     * Get externals updated field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    abstract public function getUpdatedExternalsFieldData();

    /**
     * Get externals copied field data values
     *
     * This is a PHPUnit data provider
     *
     * @return array
     */
    abstract public function getCopiedExternalsFieldData();

    /**
     * Method called after content creation
     *
     * Useful, if additional stuff should be executed (like creating the actual 
     * user).
     *
     * @param Repository\Repository $repository
     * @param Repository\Values\Content\Content $content
     * @return void
     */
    public function postCreationHook( Repository\Repository $repository, Repository\Values\Content\Content $content )
    {
        // Do nothing by default
    }

    /**
     * @depends eZ\Publish\API\Repository\Tests\RepositoryTest::testGetContentTypeService
     */
    public function testCreateContentType()
    {
        $repository         = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        $createStruct = $contentTypeService->newContentTypeCreateStruct(
            'test-' . $this->getTypeName()
        );
        $createStruct->mainLanguageCode = 'eng-GB';
        $createStruct->remoteId     = $this->getTypeName();
        $createStruct->names        = array( 'eng-GB' => 'Test' );
        $createStruct->creatorId    = 14;
        $createStruct->creationDate = new \DateTime();

        $nameFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'name', 'ezstring'
        );
        $nameFieldCreate->names      = array( 'eng-GB' => 'Title' );
        $nameFieldCreate->fieldGroup = 'main';
        $nameFieldCreate->position   = 1;
        $createStruct->addFieldDefinition( $nameFieldCreate );

        $dataFieldCreate = $contentTypeService->newFieldDefinitionCreateStruct(
            'data', $this->getTypeName()
        );
        $dataFieldCreate->names      = array( 'eng-GB' => 'Title' );
        $dataFieldCreate->fieldGroup = 'main';
        $dataFieldCreate->position   = 2;
        $createStruct->addFieldDefinition( $dataFieldCreate );

        $contentGroup     = $contentTypeService->loadContentTypeGroupByIdentifier( 'Content' );
        $contentTypeDraft = $contentTypeService->createContentType( $createStruct, array( $contentGroup ) );

        $contentTypeService->publishContentTypeDraft( $contentTypeDraft );
        $contentType = $contentTypeService->loadContentType( $contentTypeDraft->id );

        $this->assertNotNull( $contentType->id );

        return $contentType;
    }

    /**
     * @depends testCreateContentType
     */
    public function testContentTypeField( $contentType )
    {
        $this->assertSame(
            $this->getTypeName(),
            $contentType->fieldDefinitions[1]->fieldTypeIdentifier
        );
    }

    /**
     * @depends testCreateContentType
     */
    public function testLoadContentTypeField()
    {
        $contentType = $this->testCreateContentType();

        $repository         = $this->getRepository();
        $contentTypeService = $repository->getContentTypeService();

        return $contentTypeService->loadContentType( $contentType->id );
    }

    /**
     * @depends testLoadContentTypeField
     */
    public function testLoadContentTypeFieldType( $contentType )
    {
        $this->assertSame(
            $this->getTypeName(),
            $contentType->fieldDefinitions[1]->fieldTypeIdentifier
        );

        return $contentType->fieldDefinitions[1];
    }

    /**
     * @depends testLoadContentTypeFieldType
     * @dataProvider getFieldDefinitionData
     */
    public function testLoadContentTypeFieldData( $name, $value, $field )
    {
        $this->assertEquals(
            $value,
            $field->$name
        );
    }

    /**
     * @depends testLoadContentTypeField
     */
    public function testCreateContent()
    {
        // @Hack: This is required to make it possible to overwrite this
        // method, while maintaing the execution order. PHPUnit does not manage 
        // to sort tests properly, otherwise.
        if ( method_exists( $this, 'createContentOverwrite' ) )
        {
            return $this->createContentOverwrite();
        }

        $contentType = $this->testCreateContentType();

        $repository     = $this->getRepository();
        $contentService = $repository->getContentService();

        $createStruct = $contentService->newContentCreateStruct( $contentType, 'eng-US' );
        $createStruct->setField( 'name', 'Test object' );
        $createStruct->setField( 'data', $this->getInitialFieldData() );

        $createStruct->remoteId = 'abcdef0123456789abcdef0123456789';
        $createStruct->alwaysAvailable = true;

        return $contentService->createContent( $createStruct );
    }

    /**
     * @depends testCreateContent
     */
    public function testCreatedFieldType( $content )
    {
        foreach ( $content->fields as $field )
        {
            if ( $field->fieldDefIdentifier === $this->customFieldIdentifier )
            {
                return $field;
            }
        }

        $this->fail( "Custom field not found." );
    }

    /**
     * @depends testCreateContent
     */
    public function testLoadField()
    {
        $content = $this->testCreateContent();

        $repository     = $this->getRepository();
        $contentService = $repository->getContentService();
        return $contentService->loadContent( $content->contentInfo->id );
    }

    /**
     * @depends testLoadField
     */
    public function testLoadFieldType( $content )
    {
        foreach ( $content->fields as $field )
        {
            if ( $field->fieldDefIdentifier === $this->customFieldIdentifier )
            {
                return $field;
            }
        }

        $this->fail( "Custom field not found." );
    }

    /**
     * @depends testLoadFieldType
     * @dataProvider getExternalsFieldData
     */
    public function testLoadExternalData( $name, $value, $field )
    {
        if ( !array_key_exists( $name, $field->value ) )
        {
            $this->fail( "Property $name not avialable." );
        }

        $this->assertEquals(
            $value,
            $field->value[$name]
        );
    }

    /**
     * @depends testLoadFieldType
     */
    public function testUpdateField()
    {
        $content = $this->testCreateContent();

        $repository     = $this->getRepository();
        $contentService = $repository->getContentService();

        $draft = $contentService->createContentDraft( $content->contentInfo );

        $updateStruct = $contentService->newContentUpdateStruct();
        $updateStruct->setField( $this->customFieldIdentifier, $this->getUpdateFieldData() );

        return $contentService->updateContent( $draft->versionInfo, $updateStruct );
    }

    /**
     * @depends testUpdateField
     */
    public function testUpdateFieldType( $content )
    {
        foreach ( $content->fields as $field )
        {
            if ( $field->fieldDefIdentifier === $this->customFieldIdentifier )
            {
                return $field;
            }
        }

        $this->fail( "Custom field not found." );
    }

    /**
     * @depends testUpdateFieldType
     * @dataProvider getUpdatedExternalsFieldData
     */
    public function testUpdateExternalData( $name, $value, $field )
    {
        if ( !array_key_exists( $name, $field->value ) )
        {
            $this->fail( "Property $name not avialable." );
        }

        $this->assertEquals(
            $value,
            $field->value[$name]
        );
    }

    /**
     * @depends testCreateContent
     */
    public function testCopyField( $content )
    {
        $content = $this->testCreateContent();

        $repository     = $this->getRepository();
        $contentService = $repository->getContentService();

        $locationService  = $repository->getLocationService();
        $parentLocationId = $this->generateId( 'location', 2 );
        $locationCreate   = $locationService->newLocationCreateStruct( $parentLocationId );

        $copied = $contentService->copyContent( $content->contentInfo, $locationCreate );

        $this->assertNotSame(
            $content->versionInfo->contentId,
            $copied->versionInfo->contentId
        );

        return $contentService->loadContent( $copied->id );
    }

    /**
     * @depends testCopyField
     */
    public function testCopiedFieldType( $content )
    {
        foreach ( $content->fields as $field )
        {
            if ( $field->fieldDefIdentifier === $this->customFieldIdentifier )
            {
                return $field;
            }
        }

        $this->fail( "Custom field not found." );
    }

    /**
     * @depends testCopiedFieldType
     * @dataProvider getCopiedExternalsFieldData
     */
    public function testCopiedExternalData( $name, $value, $field )
    {
        if ( !array_key_exists( $name, $field->value ) )
        {
            $this->fail( "Property $name not avialable." );
        }

        $this->assertEquals(
            $value,
            $field->value[$name]
        );
    }

    /**
     * @depends testCopyField
     * @expectedException \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function testDeleteField( $content )
    {
        $content = $this->testCreateContent();

        $repository     = $this->getRepository();
        $contentService = $repository->getContentService();

        $contentService->deleteContent( $content->contentInfo );

        $contentService->loadContent( $content->contentInfo->id );
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( get_called_class() );
    }
}
