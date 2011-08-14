<?php
/**
 * File containing the Content class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence;

/**
 */
class Content extends ValueObject
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $typeId;

    /**
     * @var int
     */
    public $sectionId;

    /**
     * @var int
     */
    public $ownerId;

    /**
     * The loaded version
     *
     * The Version, containing version information and all
     * {@link \ezp\Persistence\Content\Field}s in this version (in all languages).
     * Non-translateable fields will only occur once!
     *
     * @var \ezp\Persistence\Content\Version
     */
    public $version;

    /**
     * @var \ezp\Persistence\Content\Location[]
     */
    public $locations = array();

    /**
     * @var bool Always available flag
     */
    public $alwaysAvailable;

    /**
     * @var string Remote identifier used as a custom identifier for the object
     */
    public $remoteId;
}
?>
