<?php
/**
 * File containing the BinaryFile Type class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\Repository\FieldType\BinaryFile;
use eZ\Publish\Core\Repository\FieldType\FieldType,
    eZ\Publish\API\Repository\Repository,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType,
    eZ\Publish\API\Repository\Values\IO\BinaryFile;

/**
 * The TextLine field type.
 *
 * This field type represents a simple string.
 */
class Type extends FieldType
{
    protected $allowedValidators = array(
        'eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\FileSizeValidator'
    );

    /**
     * @var \eZ\Publish\API\Repository\IOService
     */
    protected $IOService;

    /**
     * Constructs field type object, initializing internal data structures.
     *
     * @param \eZ\Publish\API\Repository\Repository $repository
     */
    public function __construct( Repository $repository )
    {
        $this->IOService = $repository->getIOService();
    }

    /**
     * Build a Value object of current FieldType
     *
     * Build a FiledType\Value object with the provided $file as value.
     *
     * @param string $file
     * @return \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function buildValue( $file )
    {
        return new Value( $this->IOService, $file );
    }

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "ezbinaryfile";
    }

    /**
     * Returns the fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     *
     * @return \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value
     */
    public function getDefaultDefaultValue()
    {
        return new Value( $this->IOService );
    }

    /**
     * Checks the type and structure of the $Value.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value $inputValue
     *
     * @return \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value
     */
    public function acceptValue( $inputValue )
    {
        if ( !$inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'eZ\\Publish\\Core\\Repository\\FieldType\\BinaryFile\\Value',
                $inputValue
            );
        }

        if ( isset( $inputValue->file ) && !$inputValue->file instanceof BinaryFile )
        {
            throw new InvalidArgumentType(
                '$inputValue->file',
                'eZ\Publish\API\Repository\Values\IO\BinaryFile',
                $inputValue->file
            );
        }

        return $inputValue;
    }

    /**
     * BinaryFile does not support sorting
     *
     * @return bool
     */
    protected function getSortInfo( $value )
    {
        return false;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value $value
     */
    public function fromHash( $hash )
    {
        return new Value( $this->IOService, $hash );
    }

    /**
     * Converts a $Value to a hash
     *
     * @param \eZ\Publish\Core\Repository\FieldType\BinaryFile\Value $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return $value->file;
    }

    /**
     * Returns whether the field type is searchable
     *
     * @return bool
     */
    public function isSearchable()
    {
        return true;
    }
}
