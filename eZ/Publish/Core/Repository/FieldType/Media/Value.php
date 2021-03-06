<?php
/**
 * File containing the Media Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\Media;
use eZ\Publish\Core\Repository\FieldType\Value as BaseValue,
    eZ\Publish\API\Repository\IOService,
    eZ\Publish\API\Repository\Exceptions\PropertyNotFoundException;

/**
 * Value for Media field type
 *
 * @property string $filename The name of the file in the eZ publish var directory
 *                            (for example "44b963c9e8d1ffa80cbb08e84d576735.avi").
 * @property string $mimeType
 */
class Value extends BaseValue
{
    /**
     * BinaryFile object
     *
     * @var \eZ\Publish\API\Repository\Values\IO\BinaryFile
     */
    public $file;

    /**
     * Original file name
     *
     * @var string
     */
    public $originalFilename;

    /**
     * The playback width - in number of pixels (for example "640").
     *
     * @var int
     */
    public $width = 0;

    /**
     * The playback height - in number of pixels (for example "480").
     *
     * @var int
     */
    public $height = 0;

    /**
     * Flag indicating to show controller or not
     *
     * @var bool
     */
    public $hasController = true;

    /**
     * Real Media specific - controls the control-bar
     *
     * @var bool
     */
    public $controls = false;

    /**
     * Automatically start playback or not
     *
     * @var bool
     */
    public $isAutoplay = true;

    /**
     * A URL that leads to the plug-in that is required for proper playback.
     *
     * @see \eZ\Publish\Core\Repository\FieldType\Media\Handler::getPluginspageByType
     * @var string
     */
    public $pluginspage;

    /**
     * Flash specific - controls the quality of the media.
     *
     * @var int
     */
    public $quality;

    /**
     * Flag indicating if media should be looped playback or single-cycle
     *
     * @var bool
     */
    public $isLoop = false;

    /**
     * @var \eZ\Publish\Core\Repository\FieldType\Media\Handler
     */
    protected $handler;

    /**
     * Construct a new Value object.
     * To affect a BinaryFile object to the $file property, use the handler:
     * <code>
     * use \eZ\Publish\Core\Repository\FieldType\Media;
     * $binaryValue = new BinaryFile\Value;
     * $binaryValue->file = $binaryValue->getHandler()->createFromLocalPath( '/path/to/local/file.txt' );
     * </code>
     *
     * @param \eZ\Publish\API\Repository\IOService $IOService
     * @param string|null $file
     */
    public function __construct( IOService $IOService, $file = null )
    {
        $this->handler = new Handler( $IOService );
        if ( $file !== null )
        {
            $this->file = $this->handler->createFromLocalPath( $file );
            $this->originalFilename = basename( $file );
        }
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value
     */
    public function __toString()
    {
        if ( !isset( $this->file->id ) )
            return "";

        return $this->file->id;
    }

    /**
     * Magic getter
     *
     * @param string $name
     */
    public function __get( $name )
    {
        switch ( $name )
        {
            case 'filename':
                return basename( $this->file->id );

            case 'mimeType':
                return $this->file->mimeType;

            case 'filesize':
                return $this->file->size;

            case 'filepath':
                return $this->file->id;

            default:
                throw new PropertyNotFoundException( $name, get_class( $this ) );
        }
    }

    /**
     * @see \eZ\Publish\Core\Repository\FieldType\Value::getTitle()
     */
    public function getTitle()
    {
        return $this->originalFilename;
    }
}
