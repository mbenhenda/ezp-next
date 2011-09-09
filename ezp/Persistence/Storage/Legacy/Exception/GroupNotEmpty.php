<?php
/**
 * File containing the StorageNotFound class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\Legacy\Exception;

/**
 * Exception thrown if a Content\Type\Group is to be deleted which is not
 * empty.
 */
class GroupNotEmpty extends \InvalidArgumentException
{
    /**
     * Creates a new exception for $groupId
     *
     * @param mixed $groupId
     */
    public function __construct( $groupId )
    {
        parent::__construct(
            sprintf( 'Group with ID "%s" is not empty.', $groupId )
        );
    }
}
