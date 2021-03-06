<?php
/**
 * File containing the TextLineTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\TextLine\Type as TextLine,
    eZ\Publish\Core\Repository\FieldType\TextLine\Value as TextLineValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class TextLineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Repository\FieldType\FieldType::allowedValidators
     */
    public function testTextLineSupportedValidators()
    {
        $ft = new TextLine();
        self::assertSame(
            array( 'eZ\\Publish\\Core\\Repository\\FieldType\\TextLine\\StringLengthValidator' ),
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Repository\FieldType\FieldType::allowedSettings
     */
    public function testTextLineAllowedSettings()
    {
        $ft = new TextLine();
        self::assertSame(
            array(),
            $ft->allowedSettings(),
            "The set of allowed settings does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\TextLine\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @group fieldType
     * @group textLine
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new TextLine();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new TextLineValue( 42 ) );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Repository\FieldType\TextLine\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new TextLine();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = new TextLineValue( 'Strings work just fine.' );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Repository\FieldType\TextLine\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $string = 'Test of FieldValue';
        $ft = new TextLine();
        $fieldValue = $ft->toPersistenceValue( new TextLineValue( $string ) );

        self::assertSame( $string, $fieldValue->data );
        self::assertSame( array( 'sort_key_string' => $string ), $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Repository\FieldType\TextLine\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $text = 'According to developers, strings are good for women health.';
        $value = new TextLineValue( $text );
        self::assertSame( $text, $value->text );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Repository\FieldType\TextLine\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new TextLineValue;
        self::assertSame( '', $value->text );
    }

    /**
     * @group fieldType
     * @group textLine
     * @covers \eZ\Publish\Core\Repository\FieldType\TextLine\Value::__toString
     */
    public function testFieldValueToString()
    {
        $string = "Believe it or not, but most geeks find strings very comfortable to work with";
        $value = new TextLineValue( $string );
        self::assertSame( $string, (string)$value );

        $value2 = new TextLineValue( (string)$value );
        self::assertSame(
            $string,
            $value2->text,
            'fromString() and __toString() must be compatible'
        );
    }
}
