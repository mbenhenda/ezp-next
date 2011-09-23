<?php
/**
 * File containing the Country Value class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\Country;
use ezp\Content\FieldType\Value as ValueInterface,
    ezp\Persistence\Content\FieldValue as PersistenceFieldValue,
    RuntimeException;

/**
 * Value for Country field type
 */
class Value implements ValueInterface
{
    /**
     * Countries values
     *
     * @var array
     */
    public $values;

    /**
     * Construct a new Value object and initialize it with its $values
     *
     * @param string[] $values
     */
    public function __construct( array $values = array() )
    {
        $this->values = $values;
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public static function fromString( $stringValue )
    {
        return new static( array( $stringValue ) );
    }

    /**
     * @see \ezp\Content\FieldType\Value
     */
    public function __toString()
    {
        return implode( "," , $this->values );
    }
}