<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\Type\ContentUpdater\Action\RemoveFieldTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\Type\ContentUpdater\Action;
use ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater\Action\RemoveField,
    ezp\Persistence\Content;

/**
 * Test case for Content Type Updater.
 */
class RemoveFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Content gateway mock
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected $contentGatewayMock;

    /**
     * RemoveField action to test
     *
     * @var ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater\Action\RemoveField
     */
    protected $removeFieldAction;

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater::__construct
     */
    public function testCtor()
    {
        $action = $this->getRemoveFieldAction();

        $this->assertAttributeSame(
            $this->getContentGatewayMock(),
            'contentGateway',
            $action
        );
        $this->assertAttributeEquals(
            $this->getFieldDefinitionFixture(),
            'fieldDefinition',
            $action
        );
    }

    /**
     * @return void
     * @covers ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater\Action\RemoveField::apply
     */
    public function testApply()
    {
        $action = $this->getRemoveFieldAction();
        $content = $this->getContentFixture();

        $this->getContentGatewayMock()->expects( $this->once() )
            ->method( 'deleteField' )
            ->with(
                $this->equalTo( 3 ),
                $this->equalTo( 13 )
            );

        $action->apply( $content );
    }

    /**
     * Returns a Content fixture
     *
     * @return \ezp\Persistence\Content
     */
    protected function getContentFixture()
    {
        $fieldNoRemove = new Content\Field();
        $fieldNoRemove->id = 2;
        $fieldNoRemove->versionNo = 13;
        $fieldNoRemove->fieldDefinitionId = 23;

        $fieldRemove = new Content\Field();
        $fieldRemove->id = 3;
        $fieldRemove->versionNo = 13;
        $fieldRemove->fieldDefinitionId = 42;

        $content = new Content();
        $content->version = new Content\Version();
        $content->version->fields = array(
            $fieldNoRemove,
            $fieldRemove
        );
        $content->version->versionNo = 3;
        return $content;
    }

    /**
     * Returns a Content Gateway mock
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Gateway
     */
    protected function getContentGatewayMock()
    {
        if ( !isset( $this->contentGatewayMock ) )
        {
            $this->contentGatewayMock = $this->getMock(
                'ezp\\Persistence\\Storage\\Legacy\\Content\\Gateway'
            );
        }
        return $this->contentGatewayMock;
    }

    /**
     * Returns a FieldDefinition fixture
     *
     * @return \ezp\Persistence\Content\Type\FieldDefinition
     */
    protected function getFieldDefinitionFixture()
    {
        $fieldDef = new Content\Type\FieldDefinition();
        $fieldDef->id = 42;
        $fieldDef->fieldType = 'ezstring';
        $fieldDef->defaultValue = new Content\FieldValue();
        return $fieldDef;
    }

    /**
     * Returns the RemoveField action to test
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\Type\ContentUpdater\Action\RemoveField
     */
    protected function getRemoveFieldAction()
    {
        if ( !isset( $this->removeFieldAction ) )
        {
            $this->removeFieldAction = new RemoveField(
                $this->getContentGatewayMock(),
                $this->getFieldDefinitionFixture()
            );
        }
        return $this->removeFieldAction;
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
