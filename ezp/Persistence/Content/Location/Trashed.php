<?php
/**
 * File containing the TrashedLocation class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Location;

use ezp\Persistence\Content\Location;

/**
 * Struct containing accessible properties on TrashedLocation entities.
 */
class Trashed extends Location
{
    /**
     * Previous Location Id (before it was trashed)
     * @param int
     */
    public $locationId;
}

