<?php
/**
 * File contains: ezp\Content\Tests\Service\TypeTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\Service;
use ezp\Content\Tests\Service\Base as BaseServiceTest,
    ezp\Content\Type\Service,
    ezp\Content\Type,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\Type\Group,
    ezp\Base\Exception\NotFound,
    Exception;

/**
 * Test case for Type service
 */
class TypeTest extends BaseServiceTest
{
    /**
     * @var \ezp\Content\Type\Service
     */
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = $this->repository->getContentTypeService();
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::createGroup
     */
    public function testCreateGroup()
    {
        $do = new Group();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do = $this->service->createGroup( $do );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $do );
        $this->assertEquals( 0, count( $do->types ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::createGroup
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testCreateGroupException()
    {
        $do = new Group();
        $do->created = $do->modified = time();
        $this->service->createGroup( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateGroup
     */
    public function testUpdateGroup()
    {
        $do = $this->service->loadGroup( 1 );
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $this->service->updateGroup( $do );
        $do = $this->service->loadGroup( 1 );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $do );
        $this->assertEquals( 1, count( $do->types ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateGroup
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testUpdateGroupException()
    {
        $do = $this->service->loadGroup( 1 );
        $do->identifier = null;
        $this->service->updateGroup( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::deleteGroup
     */
    public function testDeleteGroup()
    {
        $group = $this->service->loadGroup( 1 );
        $this->service->deleteGroup( $group );
        try
        {
            $this->service->loadGroup( 1 );
            $this->fail( "Expected exception as group being loaded has been deleted" );

        }
        catch ( NotFound $e ){}
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::loadGroup
     */
    public function testLoadGroup()
    {
        $do = $this->service->loadGroup( 1 );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $do );
        $this->assertEquals( 1, count( $do->types ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do->types[0] );
        $this->assertEquals( array( 'eng-GB' => "Content" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::loadAllGroups
     */
    public function testLoadAllGroups()
    {
        $list = $this->service->loadAllGroups();
        $do = $list[0];
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $do );
        $this->assertEquals( 1, count( $do->types ) );
        $this->assertEquals( array( 'eng-GB' => "Content type group" ), $do->description );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::create
     */
    public function testCreate()
    {
        $do = new Type();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $do->groups[] = $this->service->loadGroup( 1 );
        $do = $this->service->create( $do );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do );
        $this->assertEquals( 1, count( $do->groups ) );
        $this->assertEquals( 0, count( $do->fields ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::create
     */
    public function testCreateWithField()
    {
        $do = new Type();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $do->groups[] = $this->service->loadGroup( 1 );
        $do->fields[] = $field = new FieldDefinition( $do, 'ezstring' );
        $field->identifier = 'title';
        $field->defaultValue = 'New Test';
        $do = $this->service->create( $do );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do );
        $this->assertEquals( 1, count( $do->groups ) );
        $this->assertEquals( 1, count( $do->fields ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::create
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testCreateException()
    {
        $do = new Type();
        $do->created = $do->modified = time();
        $this->service->create( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::create
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testCreateWithoutGroup()
    {
        $do = new Type();
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $do->nameSchema = $do->urlAliasSchema = "<>";
        $do->isContainer = true;
        $do->initialLanguageId = 1;
        $this->service->create( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::update
     */
    public function testUpdate()
    {
        $do = $this->service->load( 1 );
        $do->created = $do->modified = time();
        $do->creatorId = $do->modifierId = 14;
        $do->name = $do->description = array( 'eng-GB' => 'Test' );
        $do->identifier = 'test';
        $this->service->update( $do );
        $do = $this->service->load( 1 );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $do );
        $this->assertEquals( 1, count( $do->groups ) );
        $this->assertEquals( array( 'eng-GB' => "Test" ), $do->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::update
     * @expectedException \ezp\Base\Exception\PropertyNotFound
     */
    public function testUpdateException()
    {
        $do = $this->service->load( 1 );
        $do->identifier = null;
        $this->service->update( $do );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::delete
     */
    public function testDelete()
    {
        $type = $this->service->load( 1 );
        $this->service->delete( $type );

        try
        {
            $this->service->load( 1 );
            $this->fail( "Expected exception as type being loaded has been deleted" );
        }
        catch ( NotFound $e ){}
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::load
     */
    public function testLoad()
    {
        $type = $this->service->load( 1 );

        $this->assertInstanceOf( 'ezp\\Content\\Type', $type );
        $this->assertEquals( 2, count( $type->fields ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\FieldDefinition', $type->fields[0] );
        // lazy collection tests
        $this->assertEquals( 1, count( $type->groups ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $type->groups[0] );
        $this->assertEquals( 1, count( $type->groups[0]->types ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $type->groups[0]->types[0] );
        $this->assertEquals( $type->id, $type->groups[0]->types[0]->id );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::loadByGroupId
     */
    public function testLoadByGroupId()
    {
        $list = $this->service->loadByGroupId( 1 );
        $this->assertEquals( 1, count( $list ) );

        $type = $list[0];
        $this->assertInstanceOf( 'ezp\\Content\\Type', $type );
        $this->assertEquals( 2, count( $type->fields ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\FieldDefinition', $type->fields[0] );
        // lazy collection tests
        $this->assertEquals( 1, count( $type->groups ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $type->groups[0] );
        $this->assertEquals( 1, count( $type->groups[0]->types ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $type->groups[0]->types[0] );
        $this->assertEquals( $type->id, $type->groups[0]->types[0]->id );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::copy
     */
    public function testCopy()
    {
        $type = $this->service->copy( 10, 1 );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $type );
        $this->assertStringStartsWith( 'folder_', $type->identifier );
        $this->assertEquals( 2, count( $type->fields ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\FieldDefinition', $type->fields[0] );
        // lazy collection tests
        $this->assertEquals( 1, count( $type->groups ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type\\Group', $type->groups[0] );
        $this->assertEquals( 1, count( $type->groups[0]->types ) );
        // newly created type should be draft
        $drafts = $this->service->loadByGroupId( $type->groups[0]->id, 1 );
        $this->assertEquals( 1, count( $drafts ) );
        $this->assertInstanceOf( 'ezp\\Content\\Type', $drafts[0] );
        $this->assertEquals( $type->id, $drafts[0]->id );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::copy
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testCopyInValidTypeId()
    {
        $this->service->copy( 10, 22 );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::copy
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testCopyInValidStatus()
    {
        $this->service->copy( 10, 1, 1 );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::link
     */
    public function testLink()
    {
        $newGroup = new Group();
        $newGroup->created = $newGroup->modified = time();
        $newGroup->creatorId = $newGroup->modifierId = 14;
        $newGroup->name = $newGroup->description = array( 'eng-GB' => 'Test' );
        $newGroup->identifier = 'test';
        $newGroup = $this->service->createGroup( $newGroup );
        $type = $this->service->load( 1, 0 );

        $this->service->link( $type, $newGroup );

        $type = $this->service->load( 1, 0 );
        $this->assertEquals( 2, count( $type->groups ) );
        $this->assertEquals( $newGroup->id, $type->groups[1]->id );
        $this->assertEquals( array( 'eng-GB' => 'Test' ), $type->groups[1]->name );
        $this->assertEquals( 'test', $type->groups[1]->identifier );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::link
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLinkGroupNotFound()
    {
        $newGroup = new Group();
        $newGroup->created = $newGroup->modified = time();
        $newGroup->creatorId = $newGroup->modifierId = 14;
        $newGroup->name = $newGroup->description = array( 'eng-GB' => 'Test' );
        $newGroup->identifier = 'test';

        $type = $this->service->load( 1, 0 );
        $this->service->link( $type, $newGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::link
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testLinkTypeNotFound()
    {
        $type = $this->service->load( 1, 0 );
        $existingGroup = $this->service->loadGroup( 1 );
        $this->service->delete( $type );
        $this->service->link( $type, $existingGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::link
     * @expectedException \ezp\Base\Exception\BadRequest
     */
    public function testLinkTypeAlreadyPartOfGroup()
    {
        $type = $this->service->load( 1, 0 );
        $existingGroup = $this->service->loadGroup( 1 );
        $this->service->link( $type, $existingGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::unlink
     */
    public function testUnLink()
    {
        $newGroup = new Group();
        $newGroup->created = $newGroup->modified = time();
        $newGroup->creatorId = $newGroup->modifierId = 14;
        $newGroup->name = $newGroup->description = array( 'eng-GB' => 'Test' );
        $newGroup->identifier = 'test';
        $newGroup = $this->service->createGroup( $newGroup );

        $type = $this->service->load( 1, 0 );
        $existingGroup = $this->service->loadGroup( 1 );

        $this->service->link( $type, $newGroup );
        $this->service->unlink( $type, $existingGroup );

        $type = $this->service->load( 1, 0 );
        $this->assertEquals( 1, count( $type->groups ) );
        $this->assertEquals( $newGroup->id, $type->groups[0]->id );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::unlink
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUnLinkTypeNotFound()
    {
        $type = $this->service->load( 1, 0 );
        $existingGroup = $this->service->loadGroup( 1 );
        $this->service->delete( $type );
        $this->service->unlink( $type, $existingGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::unlink
     * @expectedException \ezp\Base\Exception\BadRequest
     */
    public function testUnLinkTypeNotPartOfGroup()
    {
        $newGroup = new Group();
        $newGroup->created = $newGroup->modified = time();
        $newGroup->creatorId = $newGroup->modifierId = 14;
        $newGroup->name = $newGroup->description = array( 'eng-GB' => 'Test' );
        $newGroup->identifier = 'test';
        $newGroup = $this->service->createGroup( $newGroup );

        $type = $this->service->load( 1, 0 );
        $this->service->unlink( $type, $newGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::unlink
     * @expectedException \ezp\Base\Exception\BadRequest
     */
    public function testUnLinkLastGroup()
    {
        $type = $this->service->load( 1, 0 );
        $existingGroup = $this->service->loadGroup( 1 );
        $this->service->unlink( $type, $existingGroup );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::addFieldDefinition
     */
    public function testAddFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $field = new FieldDefinition( $type, 'ezstring' );
        $field->name = $field->description = array( 'eng-GB' => 'Test' );
        $field->defaultValue = $field->fieldGroup = '';
        $field->identifier = 'test';
        $field->isInfoCollector = $field->isRequired = $field->isTranslatable = true;
        $this->service->addFieldDefinition( $type, $field );

        $type = $this->service->load( 1, 0 );
        $this->assertEquals( 3, count( $type->fields ) );
        $this->assertEquals( 'test', $type->fields[2]->identifier );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::addFieldDefinition
     * @expectedException \ezp\Base\Exception\InvalidArgumentType
     */
    public function testAddFieldDefinitionWithExistingFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $this->service->addFieldDefinition( $type, $type->fields[0] );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::addFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testAddFieldDefinitionWithUnExistingType()
    {
        $type = $this->service->load( 1, 0 );
        $this->service->delete( $type );

        $field = new FieldDefinition( $type, 'ezstring' );
        $field->name = $field->description = array( 'eng-GB' => 'Test' );
        $field->defaultValue = $field->fieldGroup = '';
        $field->identifier = 'test';
        $field->isInfoCollector = $field->isRequired = $field->isTranslatable = true;
        $this->service->addFieldDefinition( $type, $field );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::removeFieldDefinition
     */
    public function testRemoveFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $this->service->removeFieldDefinition( $type, $type->fields[0] );

        $type = $this->service->load( 1, 0 );
        $this->assertEquals( 1, count( $type->fields ) );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::removeFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testRemoveFieldDefinitionWithUnExistingFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $field = $type->fields[0];
        $this->service->removeFieldDefinition( $type, $field );
        $this->service->removeFieldDefinition( $type, $field );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::removeFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testRemoveFieldDefinitionWithUnExistingType()
    {
        $type = $this->service->load( 1, 0 );
        $this->service->delete( $type );
        $this->service->removeFieldDefinition( $type, $type->fields[0] );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateFieldDefinition
     */
    public function testUpdateFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $type->fields[0]->name = array( 'eng-GB' => 'New name' );
        $this->service->updateFieldDefinition( $type, $type->fields[0] );

        $type = $this->service->load( 1, 0 );
        $this->assertEquals( 2, count( $type->fields ) );
        $this->assertEquals( array( 'eng-GB' => 'New name' ), $type->fields[0]->name );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUpdateFieldDefinitionWithUnExistingFieldDefinition()
    {
        $type = $this->service->load( 1, 0 );
        $type->fields[0]->name = array( 'eng-GB' => 'New name' );
        $this->service->removeFieldDefinition( $type, $type->fields[0] );
        $this->service->updateFieldDefinition( $type, $type->fields[0] );
    }

    /**
     * @group contentTypeService
     * @covers ezp\Content\Type\Service::updateFieldDefinition
     * @expectedException \ezp\Base\Exception\NotFound
     */
    public function testUpdateFieldDefinitionWithUnExistingType()
    {
        try
        {
            $type = $this->service->load( 1, 0 );
            $type->fields[0]->name = array( 'eng-GB' => 'New name' );
            $this->service->delete( $type );
        }
        catch ( Exception $e )
        {
            self::fail( "Did not expect any exception here, but got:" . $e );
        }
        $this->service->updateFieldDefinition( $type, $type->fields[0] );
    }
}
