<?php
/**
 * File containing the Policy class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\User;
use ezp\Persistence\ValueObject;

/**
 */
class Policy extends ValueObject
{
    /**
     * ID of the policy
     *
     * @var int|string
     */
    public $id;

    /**
     * Name of module, associated with the Policy
     *
     * @var string
     */
    public $module;

    /**
     * Name of the module function
     *
     * @var string
     */
    public $moduleFunction;

    /**
     * Array of policy limitations, which is just a random hash map.
     *
     * @var array|string If string, then only the value '*' is allowed, meaning all limitations.
     */
    public $limitations;
}
?>
