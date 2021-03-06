<?php
/**
 * File containing the UrlTest class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\Tests\FieldType;
use eZ\Publish\Core\Repository\FieldType\Url\Type as Url,
    eZ\Publish\Core\Repository\FieldType\Url\Value as UrlValue,
    PHPUnit_Framework_TestCase,
    ReflectionObject;

class UrlTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\FieldType::allowedValidators
     */
    public function testUrlSupportedValidators()
    {
        $ft = new Url();
        self::assertSame( array(), $ft->allowedValidators(), "The set of allowed validators does not match what is expected." );
    }

    /**
     * @covers \eZ\Publish\Core\Repository\FieldType\Url\Type::acceptValue
     * @expectedException \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @group fieldType
     */
    public function testAcceptValueInvalidFormat()
    {
        $ft = new Url();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );
        $refMethod->invoke( $ft, new UrlValue( 42 ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Url\Type::acceptValue
     */
    public function testAcceptValueValidFormat()
    {
        $ft = new Url();
        $ref = new ReflectionObject( $ft );
        $refMethod = $ref->getMethod( "acceptValue" );
        $refMethod->setAccessible( true );

        $value = new UrlValue( "http://ez.no/" );
        self::assertSame( $value, $refMethod->invoke( $ft, $value ) );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Url\Type::toPersistenceValue
     */
    public function testToPersistenceValue()
    {
        $link = "http://ez.no/";
        $ft = new Url();
        $fieldValue = $ft->toPersistenceValue( new UrlValue( $link ) );

        self::assertSame( array( "link" => $link, "text" => null ), $fieldValue->data );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Url\Value::__construct
     */
    public function testBuildFieldValueWithParam()
    {
        $link = "http://ez.no/";
        $value = new UrlValue( $link );
        self::assertSame( $link, $value->link );
    }

    /**
     * @group fieldType
     * @covers \eZ\Publish\Core\Repository\FieldType\Url\Value::__toString
     */
    public function testFieldValueToString()
    {
        $link = "http://ez.no/";
        $value = new UrlValue( $link );
        self::assertSame( $link, (string)$value );

        $value2 = new UrlValue( (string)$value );
        self::assertSame(
            $link,
            $value2->link,
            "fromString() and __toString() must be compatible"
        );
    }
}
