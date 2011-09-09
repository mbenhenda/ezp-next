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
     * One of Content::STATUS_DRAFT, Content::STATUS_PUBLISHED, Content::STATUS_ARCHIVED
     *
     * @var int Constant.
     */
    public $status;

    /**
     * @var string[]
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
     * Current Version number
     *
     * Contains the current version number of the published version.
     * If no published version exists, last draft is used, and if published version is removed, current version is
     * set to latest modified version.
     *
     * Eg: When creating a new Content object current version will point to version 1 even if it is a draft.
     *
     * @var int
     */
    public $currentVersionNo;

    /**
     * The loaded version
     *
     * The Version, containing version information and all
     * {@link \ezp\Persistence\Content\Field}s in this version (in all languages).
     * Non-translatable fields will only occur once!
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

    /**
     * TODO: Document
     *
     * @var mixed
     */
    public $initialLanguageId;
}
?>
