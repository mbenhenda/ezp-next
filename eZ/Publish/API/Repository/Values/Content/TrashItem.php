<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\Content\TrashItem class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\Content;

use eZ\Publish\API\Repository\Values\Content\Location;

/**
 *
 * this class represents a trash item, which is actually a trashed location
 */
abstract class TrashItem extends Location
{
}
