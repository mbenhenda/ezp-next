<?php
/**
 * File contains: ezp\Persistence\Tests\LocationHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\Content\Location as LocationValue,
    ezp\Persistence\Content\Location\CreateStruct,
    ezp\Persistence\Content\CreateStruct as ContentCreateStruct,
    ezp\Persistence\Content\Criterion\ContentId,
    ezp\Persistence\Content\Field,
    ezp\Base\Exception\NotFound,
    ezp\Content\Location;

/**
 * Test case for Location Handler using in memory storage.
 */
class LocationHandlerTest extends HandlerTest
{
    /**
     * Number of Content and Location generated for the tests.
     *
     * @var int
     */
    protected $entriesGenerated = 5;

    /**
     * @var \ezp\Persistence\Content\Location[]
     */
    protected $locations;

    /**
     * @var \ezp\Persistence\Content[]
     */
    protected $contents;

    /**
     * Last inserted location id in setUp
     *
     * @var int
     */
    protected $lastLocationId;

    /**
     * Last inserted content id in setUp
     *
     * @var int
     */
    protected $lastContentId;

    /**
     * Locations which should be removed in tearDown
     *
     * @var \ezp\Content\Location[]
     */
    protected $locationToDelete = array();

    /**
     * Contents which should be removed in tearDown
     *
     * @var \ezp\Content[]
     */
    protected $contentToDelete = array();

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $this->lastLocationId = 2;
        for ( $i = 0 ; $i < $this->entriesGenerated; ++$i )
        {
            $this->contents[] = $content = $this->repositoryHandler->contentHandler()->create(
                new ContentCreateStruct(
                    array(
                        "name" => array( "eng-GB" => "test_$i" ),
                        "ownerId" => 14,
                        "sectionId" => 1,
                        "typeId" => 2,
                        "fields" => array(
                            new Field(
                                array(
                                    "type" => "ezstring",
                                    // @todo Use FieldValue object
                                    "value" => "Welcome $i",
                                    "language" => "eng-GB",
                                )
                            )
                        )
                    )
                )
            );

            $this->lastContentId = $content->id;

            $this->locations[] = $location = $this->repositoryHandler->locationHandler()->create(
                new CreateStruct(
                    array(
                        "contentId" => $this->lastContentId,
                        "contentVersion" => 1,
                        "mainLocationId" => $this->lastLocationId,
                        "sortField" => Location::SORT_FIELD_NAME,
                        "sortOrder" => Location::SORT_ORDER_ASC,
                        "parentId" => $this->lastLocationId,
                    )
                )
            );

            $this->lastLocationId = $location->id;
        }

        $this->locationToDelete = $this->locations;
        $this->contentToDelete = $this->contents;
    }

    /**
     * Removes stuff created in setUp().
     */
    protected function tearDown()
    {
        $locationHandler = $this->repositoryHandler->locationHandler();

        // Removing default objects as well as those created by tests
        foreach ( $this->locationToDelete as $location )
        {
            try
            {
                $locationHandler->removeSubtree( $location->id );
            }
            catch ( NotFound $e )
            {
            }
        }

        $contentHandler = $this->repositoryHandler->contentHandler();
        foreach ( $this->contentToDelete as $content )
        {
            try
            {
                $contentHandler->delete( $content->id );
            }
            catch ( NotFound $e )
            {
            }
        }

        unset( $this->lastLocationId, $this->lastContentId );
        parent::tearDown();
    }

    /**
     * Test load function
     *
     * @covers \ezp\Persistence\Storage\InMemory\LocationHandler::load
     * @group locationHandler
     */
    public function testLoad()
    {
        $location = $this->repositoryHandler->locationHandler()->load( $this->lastLocationId );
        $this->assertTrue( $location instanceof LocationValue );
        $this->assertEquals( $this->lastLocationId, $location->id );
        $this->assertEquals( $this->lastContentId, $location->contentId );
        // @todo contentVersion not yet implemented
        //$this->assertEquals( 1, $location->contentVersion );
        $this->assertEquals( "test_0/test_1/test_2/test_3/test_4", $location->pathIdentificationString );
        $this->assertEquals( $this->lastLocationId, $location->mainLocationId );
        $this->assertEquals( Location::SORT_FIELD_NAME, $location->sortField );
        $this->assertEquals( Location::SORT_ORDER_ASC, $location->sortOrder );
        $this->assertEquals( $this->lastLocationId - 1, $location->parentId );
    }

    /**
     * Test create function
     *
     * @covers \ezp\Persistence\Storage\InMemory\LocationHandler::create
     * @group locationHandler
     */
    public function testCreate()
    {
        $location = $this->repositoryHandler->locationHandler()->create(
            new CreateStruct(
                array(
                    "contentId" => 1,
                    "contentVersion" => 1,
                    "pathIdentificationString" => "",
                    "mainLocationId" => 2,
                    "sortField" => Location::SORT_FIELD_NAME,
                    "sortOrder" => Location::SORT_ORDER_ASC,
                    "parentId" => 1,
                )
            )
        );
        $this->locationToDelete[] = $location;
        $this->assertTrue( $location instanceof LocationValue );
        $this->assertEquals( $this->lastLocationId + 1, $location->id );
        $this->assertEquals( 1, $location->contentId );
        // @todo contentVersion not yet implemented
        //$this->assertEquals( 1, $location->contentVersion );
        $this->assertEmpty( $location->pathIdentificationString );
        $this->assertEquals( 2, $location->mainLocationId );
        $this->assertEquals( Location::SORT_FIELD_NAME, $location->sortField );
        $this->assertEquals( Location::SORT_ORDER_ASC, $location->sortOrder );
        $this->assertEquals( 1, $location->parentId );
    }

    /**
     * Test removeSubtree function with no children
     *
     * @covers \ezp\Persistence\Storage\InMemory\LocationHandler::removeSubtree
     * @group locationHandler
     */
    public function testRemoveSubtreeNoChildren()
    {
        $locationHandler = $this->repositoryHandler->locationHandler();
        $locationHandler->removeSubtree( $this->lastLocationId );

        try
        {
            $locationHandler->load( $this->lastLocationId );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test removeSubtree function with children
     *
     * @covers \ezp\Persistence\Storage\InMemory\LocationHandler::removeSubtree
     * @group locationHandler
     */
    public function testRemoveSubtreeChildren()
    {
        $locationHandler = $this->repositoryHandler->locationHandler();
        $locationHandler->removeSubtree( $this->lastLocationId - 2 );

        try
        {
            $locationHandler->load( $this->lastLocationId );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }

        try
        {
            $locationHandler->load( $this->lastLocationId - 1 );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }

        try
        {
            $locationHandler->load( $this->lastLocationId - 2 );
            $this->fail( "Content not removed correctly" );
        }
        catch ( NotFound $e )
        {
        }
    }

    /**
     * Test copySubtree function with no children
     *
     * @covers \ezp\Persistence\Storage\InMemory\LocationHandler::copySubtree
     * @group locationHandler
     */
    public function testCopySubtreeNoChildren()
    {
        // Copy the last location created in setUp
        $newLocation = $this->repositoryHandler->locationHandler()->copySubtree( $this->lastLocationId, 1 );
        $this->assertTrue( $newLocation instanceof LocationValue );
        $this->assertEquals( $this->lastLocationId + 1 , $newLocation->id );
        $this->assertEquals( $this->lastContentId + 1, $newLocation->contentId );
        $this->assertEquals( 1, $newLocation->depth );
        $this->assertEquals( Location::SORT_FIELD_NAME, $newLocation->sortField );
        $this->assertEquals( Location::SORT_ORDER_ASC, $newLocation->sortOrder );

        $this->assertEquals(
            $this->repositoryHandler->searchHandler()->findSingle(
                new ContentId( $newLocation->contentId )
            )->locations[0],
            $newLocation,
            "Location does not match"
        );
    }

    /**
     * Test copySubtree function with children
     *
     * @covers \ezp\Persistence\Storage\InMemory\LocationHandler::copySubtree
     * @group locationHandler
     */
    public function testCopySubtreeChildren()
    {
        // Copy the grand parent of the last location created in setUp
        $newLocation = $this->repositoryHandler->locationHandler()->copySubtree( $this->lastLocationId - 2, 1 );
        $this->assertTrue( $newLocation instanceof LocationValue );
        $this->assertEquals( $this->lastLocationId + 1 , $newLocation->id );
        $this->assertEquals( $this->lastContentId + 1, $newLocation->contentId );
        $this->assertEquals( 1, $newLocation->depth );
        $this->assertEquals( Location::SORT_FIELD_NAME, $newLocation->sortField );
        $this->assertEquals( Location::SORT_ORDER_ASC, $newLocation->sortOrder );

        // Verifying the deepest child is present
        foreach (
            $this->repositoryHandler->searchHandler()->findSingle(
                new ContentId( $newLocation->contentId )
            )->locations[0] as $property => $value
        )
        {
            switch ( $property )
            {
                case 'modifiedSubLocation' :
                    self::assertGreaterThanOrEqual( $newLocation->$property, $value, "Location does not match" );
                    break;

                default:
                    self::assertEquals( $newLocation->$property, $value, "Location does not match" );
            }
        }

        // Verifying the direct child is present
        $loc = $this->repositoryHandler->locationHandler()->load( $newLocation->id - 1 );
        foreach (
            $this->repositoryHandler->searchHandler()->findSingle(
                new ContentId( $newLocation->contentId - 1 )
            )->locations[0] as $property => $value
        )
        {
            switch ( $property )
            {
                case 'modifiedSubLocation' :
                    self::assertGreaterThanOrEqual( $loc->$property, $value, "Location does not match" );
                    break;

                default:
                    self::assertEquals( $loc->$property, $value, "Location does not match" );
            }
        }
        unset( $loc );

        // Verifying the top most copied location (the grand parent) is present
        $loc = $this->repositoryHandler->locationHandler()->load( $newLocation->id - 2 );
        foreach (
            $this->repositoryHandler->searchHandler()->findSingle(
                new ContentId( $newLocation->contentId - 2 )
            )->locations[0] as $property => $value
        )
        {
            switch ( $property )
            {
                case 'modifiedSubLocation' :
                    self::assertGreaterThanOrEqual( $loc->$property, $value, "Location does not match" );
                    break;

                default:
                    self::assertEquals( $loc->$property, $value, "Location does not match" );
            }
        }
    }

    /**
     * Tests loadByParentId function with no children
     *
     * @covers \ezp\Persistence\Storage\InMemory\LocationHandler::loadByParentId
     * @group locationHandler
     */
    public function testLoadByParentIdNoChildren()
    {
        $this->assertEmpty( $this->repositoryHandler->locationHandler()->loadByParentId( $this->lastLocationId ) );
    }

    /**
     * Tests loadByParentId function with children
     *
     * @covers \ezp\Persistence\Storage\InMemory\LocationHandler::loadByParentId
     * @group locationHandler
     */
    public function testLoadByParentIdChildren()
    {
        $this->assertEquals(
            array( end( $this->locations ) ),
            $this->repositoryHandler->locationHandler()->loadByParentId( $this->lastLocationId - 1 )
        );
    }

    /**
     * Tests loadByParentId function on unexisting id
     *
     * @expectedException \ezp\Base\Exception\NotFound
     * @covers \ezp\Persistence\Storage\InMemory\LocationHandler::loadByParentId
     * @group locationHandler
     */
    public function testLoadByParentIdNotExisting()
    {
        $this->repositoryHandler->locationHandler()->loadByParentId( 123456 );
    }
}
