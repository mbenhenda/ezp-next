<?php
/**
 * File containing the Language create struct class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Content\Language;
use ezp\Persistence\ValueObject;

/**
 * Struct containing accessible properties when creating Language entities.
 */
class CreateStruct extends ValueObject
{
    /**
     * Language Code (eg: eng-GB)
     *
     * @var string
     */
    public $locale;

    /**
     * Human readable language name
     *
     * @var string
     */
    public $name;

    /**
     * Indicates if language is enabled or not
     *
     * @var bool
     */
    public $isEnabled = true;
}
