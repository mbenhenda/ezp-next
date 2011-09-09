<?php
/**
 * File contains: ezp\Content\Tests\RelationTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content,
    ezp\Content\Type,
    ezp\Content\Relation,
    ezp\Persistence\Content\Relation as RelationValue,
    PHPUnit_Framework_TestCase,
    ezp\User;

/**
 * Test case for Relation class
 */
class RelationTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var \ezp\Content
     */
    protected $content;

    public function setUp()
    {
        parent::setUp();

        // setup a content type & content object of use by tests, fields are not needed for relation
        $contentType = new Type();
        $contentType->identifier = "article";

        $this->content = new Content( $contentType, new User( 10 ) );
        $this->content->setState(
            array(
                "properties" => new RelationValue(
                    array(
                        "id" => 42,
                    )
                )
            )
        );
    }

    /**
     * @covers \ezp\Content\Relation::__construct
     */
    public function testConstruct()
    {
        $relation = new Relation( Relation::COMMON, $this->content );
        $this->assertEquals( 42, $relation->destinationContentId );
        $this->assertEquals( Relation::COMMON, $relation->type );
    }

    /**
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     * @covers \ezp\Content\Relation::__construct
     */
    public function testConstructWrongType1()
    {
        $relation = new Relation( "common", $this->content );
    }

    /**
     * @expectedException \ezp\Base\Exception\InvalidArgumentValue
     * @covers \ezp\Content\Relation::__construct
     */
    public function testConstructWrongType2()
    {
        $relation = new Relation( ~0, $this->content );
    }
}
