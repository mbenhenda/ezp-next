<?php
/**
 * File containing the TextLineConverterLegacy class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests\FieldType;
use ezp\Content\FieldType\Factory,
    ezp\Content\FieldType\TextLine\Type as TextLine,
    ezp\Content\FieldType\TextLine\Value as TextLineValue,
    ezp\Persistence\Content\FieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldValue,
    ezp\Persistence\Storage\Legacy\Content\StorageFieldDefinition,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\TextLine as TextLineConverter,
    ezp\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;

/**
 * Test case for TextLine converter in Legacy storage
 */
class TextLineConverterLegacyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\TextLine
     */
    protected $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = new TextLineConverter;
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\TextLine::toStorageValue
     */
    public function testToStorageValue()
    {
        $value = new FieldValue;
        $value->data = new TextLineValue( "He's holding a thermal detonator!" );
        $value->sortKey = array( 'sort_key_string' => "He's holding" );
        $storageFieldValue = new StorageFieldValue;

        $this->converter->toStorageValue( $value, $storageFieldValue );
        self::assertSame( $value->data->text, $storageFieldValue->dataText );
        self::assertSame( $value->sortKey['sort_key_string'], $storageFieldValue->sortKeyString );
        self::assertSame( 0, $storageFieldValue->sortKeyInt );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\TextLine::toFieldValue
     */
    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue;
        $storageFieldValue->dataText = 'When 900 years old, you reach... Look as good, you will not.';
        $storageFieldValue->sortKeyString = 'When 900 years old, you reach...';
        $storageFieldValue->sortKeyInt = 0;
        $fieldValue = new FieldValue;

        $this->converter->toFieldValue( $storageFieldValue, $fieldValue );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\TextLine\\Value', $fieldValue->data );
        self::assertSame( $storageFieldValue->dataText, $fieldValue->data->text );
        self::assertSame( $storageFieldValue->sortKeyString, $fieldValue->sortKey['sort_key_string'] );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\TextLine::toStorageFieldDefinition
     */
    public function testToStorageFieldDefinition()
    {
        $storageFieldDef = new StorageFieldDefinition;
        $defaultValue = new FieldValue;
        $defaultValue->data = new TextLineValue( 'This is a default value' );
        $fieldDef = new PersistenceFieldDefinition(
            array(
                'fieldTypeConstraints' => array(
                    TextLineConverter::STRING_LENGTH_VALIDATOR_FQN =>  array( 'maxStringLength' => 100 )
                ),
                'defaultValue' => $defaultValue
            )
        );

        $this->converter->toStorageFieldDefinition( $fieldDef, $storageFieldDef );
        self::assertSame(
            $fieldDef->fieldTypeConstraints[TextLineConverter::STRING_LENGTH_VALIDATOR_FQN],
            array( 'maxStringLength' => $storageFieldDef->dataInt1 )
        );
        self::assertSame( $fieldDef->defaultValue->data->text, $storageFieldDef->dataText1 );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\TextLine::toFieldDefinition
     */
    public function testToFieldDefinition()
    {
        $fieldDef = new PersistenceFieldDefinition;
        $storageDef = new StorageFieldDefinition(
            array(
                'dataInt1' => 100,
                'dataText1' => 'This is a default value'
            )
        );

        $this->converter->toFieldDefinition( $storageDef, $fieldDef );
        self::assertSame(
            array(
                TextLineConverter::STRING_LENGTH_VALIDATOR_FQN => array( 'maxStringLength' => 100 )
            ),
            $fieldDef->fieldTypeConstraints
        );
        self::assertInstanceOf( 'ezp\\Content\\FieldType\\TextLine\\Value', $fieldDef->defaultValue->data );
        self::assertSame( 'This is a default value', $fieldDef->defaultValue->data->text );
    }
}