<?php
/**
 * File containing the FieldSettings class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType;
use \ArrayObject,
    ezp\Base\Exception\PropertyPermission;

/**
 * Container for field type specific properties.
 *
 * @internal
 */
class FieldSettings extends ArrayObject
{
    /**
     * Only allows existing indexes to be updated.
     *
     * This is so that only settings specified by a field type can be set.
     *
     * @internal
     * @throws ezp\Base\Exception\PropertyPermission
     * @param string|int $index
     * @param mixed $value
     * @return void
     */
    public function offsetSet( $index, $value )
    {
        if ( !parent::offsetExists( $index ) )
        {
            throw new PropertyPermission( $index, PropertyPermission::WRITE, __CLASS__ );
        }
           parent::offsetSet( $index, $value );
    }
}
