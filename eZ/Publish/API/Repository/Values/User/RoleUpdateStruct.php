<?php
/**
 * File containing the eZ\Publish\API\Repository\Values\User\RoleUpdateStruct class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\Repository\Values\User;
use eZ\Publish\API\Repository\Values\ValueObject;

/**
 * This class is used to update a role
 */
class RoleUpdateStruct extends ValueObject
{
    /**
     * Readable string identifier of a role
     *
     * @var string
     */
    public $identifier;

    /**
     * the main language code
     *
     * @since 5.0
     *
     * @var string
     */
    public $mainLanguageCode;

   /**
     * An array of names with languageCode keys
     *
     * @since 5.0
     *
     * @var array an array of string
     */
    public $names;

    /**
     * An array of descriptions with languageCode keys
     *
     * @since 5.0
     *
     * @var array an array of string
     */
    public $descriptions;

}
