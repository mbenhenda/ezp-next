<?php
/**
 * File containing the CheckboxTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Checkbox\Type as Checkbox,
    eZ\Publish\Core\Repository\FieldType\Checkbox\Value as CheckboxValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class CheckboxTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\FieldType::allowedValidators
     */
    public function testCheckboxSupportedValidators()
    {
        $ft = new Checkbox();
        self::assertSame(
            array(),
            $ft->allowedValidators(),
            "The set of allowed validators does not match what is expected."
        );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\FieldType::allowedSettings
     */
    public function testCheckboxAllowedSettings()
    {
        $ft = new Checkbox();
        self::assertSame(
            array( 'defaultValue' ),
            $ft->allowedSettings(),
            "The set of allowed settings does not match what is expected."
        );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @group fieldType
     * @group ezboolean
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Checkbox();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new CheckboxValue( 'I am definitely not a boolean' ) );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @group fieldType
     * @group ezboolean
     */
    public function testAcceptValueInvalidValue()
    {
        $ft = new Checkbox();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, $this->getMock( 'eZ\\Publish\\Core\\Repository\\FieldType\\Value' ) );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Checkbox();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( 'acceptValue' );
        $refMethod->setAccessible( true );

        $value = new CheckboxValue( true );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $ft = new Checkbox();
        $fieldValue = $ft->toPersistenceValue( new CheckboxValue( true ) );

        self::assertSame( true, $fieldValue->data );
        self::assertSame( array( 'sort_key_int' => 1 ), $fieldValue->sortKey );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $bool = true;
        $value = new CheckboxValue( $bool );
        self::assertSame( $bool, $value->bool );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Value::__construct
     */
    public function testBuildFieldValueWithoutParam()
    {
        $value = new CheckboxValue;
        self::assertSame( false, $value->bool );
    }

    /**
     * @group fieldType
     * @group ezboolean
     * @covers \eZ\Publish\Core\Repository\FieldType\Checkbox\Value::__toString
     */
    public function testFieldValueToString()
    {
        $valueTrue = new CheckboxValue( true );
        $valueFalse = new CheckboxValue( false );
        self::assertSame( '1', (string)$valueTrue );
        self::assertSame( '0', (string)$valueFalse );
    }
}
