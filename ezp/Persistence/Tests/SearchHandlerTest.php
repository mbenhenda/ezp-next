<?php
/**
 * File contains: ezp\Persistence\Tests\SearchHandlerTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Tests;
use ezp\Persistence\Content,
    ezp\Persistence\Content\CreateStruct,
    ezp\Persistence\Content\Field,
    ezp\Persistence\Content\Criterion\ContentId,
    ezp\Base\Exception\NotFound;

/**
 * Test case for SearchHandler using in memory storage.
 *
 */
class SearchHandlerTest extends HandlerTest
{
    /**
     * @var \ezp\Content
     */
    protected $content;

    /**
     *
     * @var int
     */
    protected $contentId;

    /**
     * @var \ezp\Content[]
     */
    protected $contentToDelete = array();

    /**
     * Setup the HandlerTest.
     */
    protected function setUp()
    {
        parent::setUp();

        $struct = new CreateStruct();
        $struct->name = "test";
        $struct->ownerId = 14;
        $struct->sectionId = 1;
        $struct->typeId = 2;
        $struct->fields[] = new Field(
            array(
                "type" => "ezstring",
                // @todo Use FieldValue object
                "value" => "Welcome",
                "language" => "eng-GB",
            )
        );

        $this->content = $this->repositoryHandler->contentHandler()->create( $struct );
        $this->contentToDelete[] = $this->content;
        $this->contentId = $this->content->id;
    }

    protected function tearDown()
    {
        $contentHandler = $this->repositoryHandler->contentHandler();

        try
        {
            // Removing default objects as well as those created by tests
            foreach ( $this->contentToDelete as $content )
            {
                $contentHandler->delete( $content->id );
            }
        }
        catch ( NotFound $e )
        {
        }
        unset( $this->contentId );
        parent::tearDown();
    }

    /**
     * Test findSingle function
     *
     * @covers ezp\Persistence\Storage\InMemory\SearchHandler::findSingle
     */
    public function testFindSingle()
    {
        $content = $this->repositoryHandler->searchHandler()->findSingle( new ContentId( $this->content->id ) );
        $this->assertTrue( $content instanceof Content );
        $this->assertEquals( $this->contentId, $content->id );
        $this->assertEquals( 14, $content->ownerId );
        $this->assertEquals( "test", $content->name );
        $this->assertInstanceOf( "ezp\\Persistence\\Content\\Version", $content->version );
    }
}
