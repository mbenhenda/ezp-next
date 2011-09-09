<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Section\Gateway\EzcDatabaseTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Section\Gateway;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase,

    ezp\Persistence\Content\Section;

/**
 * Test case for ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase.
 */
class EzcDatabaseTest extends TestCase
{
    /**
     * Database gateway to test.
     *
     * @var ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase
     */
    protected $databaseGateway;

    /**
     * Inserts DB fixture.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/sections.php'
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase::__construct
     */
    public function testCtor()
    {
        $handler = $this->getDatabaseHandler();
        $gateway = $this->getDatabaseGateway();

        $this->assertAttributeSame(
            $handler,
            'dbHandler',
            $gateway
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase::insertSection
     */
    public function testInsertSection()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->insertSection( 'New Section', 'new_section' );

        $this->assertQueryResult(
            array(
                array(
                    'id' => '7',
                    'identifier' => 'new_section',
                    'name' => 'New Section',
                    'locale' => '',
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'id', 'identifier', 'name', 'locale' )
                ->from( 'ezsection' )
                ->where( 'identifier="new_section"' )
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase::updateSection
     */
    public function testUpdateSection()
    {
        $gateway = $this->getDatabaseGateway();

        $gateway->updateSection( 2, 'New Section', 'new_section' );

        $this->assertQueryResult(
            array(
                array(
                    'id' => '2',
                    'identifier' => 'new_section',
                    'name' => 'New Section',
                    'locale' => '',
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'id', 'identifier', 'name', 'locale' )
                ->from( 'ezsection' )
                ->where( 'id=2' )
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase::loadSectionData
     */
    public function testLoadSectionData()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->loadSectionData( 2 );

        $this->assertEquals(
            array(
                array(
                    'id' => '2',
                    'identifier' => 'users',
                    'name' => 'Users',
                )
            ),
            $result
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase::countContentObjectsInSection
     */
    public function testCountContentObjectsInSection()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $result = $gateway->countContentObjectsInSection( 2 );

        $this->assertSame(
            7,
            $result
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase::deleteSection
     */
    public function testDeleteSection()
    {
        $gateway = $this->getDatabaseGateway();

        $result = $gateway->deleteSection( 2 );

        $this->assertQueryResult(
            array(
                array(
                    'count' => '5'
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'COUNT( * ) AS count' )
                ->from( 'ezsection' )
        );

        $this->assertQueryResult(
            array(
                array(
                    'count' => '0'
                )
            ),
            $this->getDatabaseHandler()->createSelectQuery()
                ->select( 'COUNT( * ) AS count' )
                ->from( 'ezsection' )
                ->where( 'id=2' )
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase::assignSectionToContent
     * @depends testCountContentObjectsInSection
     */
    public function testAssignSectionToContent()
    {
        $this->insertDatabaseFixture(
            __DIR__ . '/../../_fixtures/contentobjects.php'
        );

        $gateway = $this->getDatabaseGateway();

        $beforeCount = $gateway->countContentObjectsInSection( 4 );

        $result = $gateway->assignSectionToContent( 4, 10 );

        $this->assertSame(
            $beforeCount + 1,
            $gateway->countContentObjectsInSection( 4 )
        );
    }

    /**
     * Returns a ready to test EzcDatabase gateway
     *
     * @return ezp\Persistence\Storage\Legacy\Content\Section\Gateway\EzcDatabase
     */
    protected function getDatabaseGateway()
    {
        if ( !isset( $this->databaseGateway ) )
        {
            $this->databaseGateway = new EzcDatabase(
                 $this->getDatabaseHandler()
            );
        }
        return $this->databaseGateway;
    }

    /**
     * Returns the test suite with all tests declared in this class.
     *
     * @return \PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        return new \PHPUnit_Framework_TestSuite( __CLASS__ );
    }
}
