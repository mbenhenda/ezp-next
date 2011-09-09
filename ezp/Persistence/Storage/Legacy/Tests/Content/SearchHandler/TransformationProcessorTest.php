<?php
/**
 * File contains: ezp\Persistence\Storage\Legacy\Tests\Content\SearchHandler/TransformationProcessorTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Tests\Content\SearchHandler;
use ezp\Persistence\Storage\Legacy\Tests\TestCase,
    ezp\Persistence\Storage\Legacy\Content\Search;

/**
 * Test case for LocationHandlerTest
 */
class TransformationProcessorTest extends TestCase
{
    public function getProcessor()
    {
        $processor = new Search\TransformationProcessor(
            new Search\TransformationParser(),
            new Search\TransformationPcreCompiler( new Search\Utf8Converter() )
        );

        foreach ( glob( __DIR__ . '/_fixtures/transformations/*.tr' ) as $file )
        {
            $processor->loadRules( $file );
        }
        return $processor;
    }

    public function testSimpleNormalizationLowercase()
    {
        $processor = $this->getProcessor();

        $this->assertSame(
            'hello world!',
            $processor->transform( 'Hello World!', array( 'ascii_lowercase' ) )
        );
    }

    public function testSimpleNormalizationUppercase()
    {
        $processor = $this->getProcessor();

        $this->assertSame(
            'HELLO WORLD!',
            $processor->transform( 'Hello World!', array( 'ascii_uppercase' ) )
        );
    }

    /**
     * The main point of this test is, that it shows that all normalizations
     * available can be compiled without errors. The actual expectation is not
     * important.
     */
    public function testAllNormalizations()
    {
        $processor = $this->getProcessor();

        $this->assertSame(
            'HELLO WORLD.',
            $processor->transform( 'Hello World!' )
        );
    }
}

