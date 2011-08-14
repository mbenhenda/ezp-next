<?php
/**
 * File contains: ezp\Base\Tests\IniParserTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base\Tests;
use ezp\Base\Configuration\Parser\Ini,
    ezp\Base\Configuration;

/**
 * Test case for Parser\Ini class
 *
 */
class IniParserTest extends \PHPUnit_Framework_TestCase
{
    protected $parser;

    public function setUp()
    {
        parent::setUp();
        $this->parser = new Ini( null );
    }

    /**
     * Test that ending hash boom is stripped out
     * @covers \ezp\Base\Configuration\Parser\Ini::parseFilePhp
     * @covers \ezp\Base\Configuration\Parser\Ini::parseFileEzc
     */
    public function testHashBoom()
    {
        $iniString = '
[test]
HashBoomer=enabled##!';
        $expects = array( 'test' => array( 'HashBoomer' => 'enabled' ) );
        $result = $this->parser->parseFilePhp( $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'parse_ini_string based ini parser did not strip hash boom'
        );

        $result = $this->parser->parseFileEzc( $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'ezcConfigurationIniReader based ini parser did not strip hash boom'
        );
    }

    /**
     * Test that types in ini is properly parsed to native php types
     * @covers \ezp\Base\Configuration\Parser\Ini::parseFilePhpPostFilter
     * @covers \ezp\Base\Configuration\Parser\Ini::parseFilePhp
     * @covers \ezp\Base\Configuration\Parser\Ini::parseFileEzc
     */
    public function testTypes()
    {
        $iniString = '
[test]
Int=1
Float=5.4
Decimal=5,4
BoolTrue=true
BoolFalse=false
BoolEnabled=enabled
BoolDisabled=disabled
String=Test';
        $expects = array(
            'test' => array(
                'Int' => 1,
                'Float' => 5.4,
                'Decimal' => '5,4',
                'BoolTrue' => true,
                'BoolFalse' => false,
                'BoolEnabled' => 'enabled',
                'BoolDisabled' => 'disabled',
                'String' => 'Test',
            )
        );
        $result = $this->parser->parseFilePhp( $iniString );
        $this->assertSame(
            $expects,
            $result,
            'parse_ini_string based ini parser did not cast type properly'
        );

        $result = $this->parser->parseFileEzc( $iniString );
        $this->assertSame(
            $expects,
            $result,
            'ezcConfigurationIniReader based ini parser did not cast type properly'
        );
    }

    /**
     * Test that types in ini is properly parsed to native php types in arrays
     * @covers \ezp\Base\Configuration\Parser\Ini::parseFilePhp
     * @covers \ezp\Base\Configuration\Parser\Ini::parseFileEzc
     */
    public function testArrayTypes()
    {
        $iniString = '
[test]
Mixed[]=true
Mixed[]=false
Mixed[]=string
Mixed[]=44
Mixed[]=4.4
Mixed[]=4,4';
        $expects = array( 'test' => array( 'Mixed' => array( true, false, 'string', 44, 4.4, '4,4' ) ) );
        $result = $this->parser->parseFilePhp( $iniString );
        $this->assertSame(
            $expects,
            $result,
            'parse_ini_string based ini parser did not cast type properly'
        );

        $result = $this->parser->parseFileEzc( $iniString );
        $this->assertSame(
            $expects,
            $result,
            'ezcConfigurationIniReader based ini parser did not cast type properly'
        );
    }

    /**
     * Test that empty arrays are returned
     * @covers \ezp\Base\Configuration\Parser\Ini::parseFilePhp
     * @covers \ezp\Base\Configuration\Parser\Ini::parseFileEzc
     */
    public function testEmptyArray()
    {
        $iniString = '
[test]
empty-array[]';
        $expects = array( 'test' => array( 'empty-array' => array( Configuration::TEMP_INI_UNSET_VAR ) ) );
        $result = $this->parser->parseFilePhp( $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'parse_ini_string based ini parser did not return empty array'
        );

        $result = $this->parser->parseFileEzc( $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'ezcConfigurationIniReader based ini parser did not return empty array'
        );
    }

    /**
     * Test that complex hash structures with symbol use in key and value are parsed
     * @covers \ezp\Base\Configuration\Parser\Ini::parseFilePhp
     * @covers \ezp\Base\Configuration\Parser\Ini::parseFileEzc
     */
    public function testComplexHash()
    {
        $iniString = '
[test]
conditions[ezp\\system\\Filter_Get::dev]=uri\\0:content\\uri\\1:^v\\auth:?php\\params:%php
conditions[$user_object->check]=ezp/system/router\\ezp\\system\\Filter_Get::dev
conditions[]=uri\\0:§£$content';
        $expects = array(
            'test' => array(
                'conditions' => array(
                    'ezp\\system\\Filter_Get::dev' => 'uri\\0:content\\uri\\1:^v\\auth:?php\\params:%php',
                    '$user_object->check' => 'ezp/system/router\\ezp\\system\\Filter_Get::dev',
                    'uri\\0:§£$content'
                )
            )
        );
        $result = $this->parser->parseFilePhp( $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'parse_ini_string based ini parser did not parse complex hash'
        );

        $result = $this->parser->parseFileEzc( $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'ezcConfigurationIniReader based ini parser did not parse complex hash'
        );
    }

    /**
     * Test that arrays contain clearing hint to Configuration class
     * @covers \ezp\Base\Configuration\Parser\Ini::parserClearArraySupport
     */
    public function testArrayClearing()
    {
        $iniString = '
[test]
sub[]=hi
sub[]';
        $expects = array( 'test' => array( 'sub' => array( 'hi', Configuration::TEMP_INI_UNSET_VAR ) ) );
        $result = $this->parser->parseFilePhp( $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'parse_ini_string based ini parser did not properly clear array'
        );

        $result = $this->parser->parseFileEzc( $iniString );
        $this->assertEquals(
            $expects,
            $result,
            'ezcConfigurationIniReader based ini parser did not properly clear array'
        );
    }
}
