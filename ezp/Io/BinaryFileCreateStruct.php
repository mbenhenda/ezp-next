<?php
/**
 * File containing the \ezp\Persistence\BinaryFileCreateStruct class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io;

/**
 * Create struct for BinaryFile objects
 */
class BinaryFileCreateStruct extends BinaryFile
{
    /**
     * Returns the file's input resource
     * @return resource
     */
    public function getInputStream()
    {
        return $this->inputStream;
    }

    /**
     * Sets the file's input resource
     * @param resource $inputStream
     */
    public function setInputStream( $inputStream )
    {
        $this->inputStream = $inputStream;
    }

    private $inputStream;
}
