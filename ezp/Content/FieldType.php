<?php
/**
 * File containing the FieldType class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content;
use ezp\Content\FieldType\FieldSettings,
    ezp\Persistence\Content\FieldValue,
    ezp\Content\Type\FieldDefinition,
    ezp\Base\Configuration,
    ezp\Base\Exception\MissingClass;

/**
 * Base class for field types, the most basic storage unit of data inside eZ Publish.
 *
 * All other field types extend FieldType providing the specific functionality
 * desired in each case.
 *
 * The capabilities supported by each individual field type is decided by which
 * interfaces the field type implements support for. These individual
 * capabilities can also be checked via the supports*() methods.
 *
 * A field type are the base building blocks of Content Types, and serve as
 * data containers for Content objects. Therefore, while field types can be used
 * independently, they are designed to be used as a part of a Content object.
 *
 * Field types are primed and pre-configured with the Field Definitions found in
 * Content Types.
 *
 * @todo Merge and optimize concepts for settings, validator data and field type properties.
 */
abstract class FieldType
{
    /**
     * @var string The textual identifier of the field type.
     */
    protected $fieldTypeString;

    /**
     * @var mixed Fallback default value of field type when no such default
     * value is provided in the field definition in content types.
     */
    protected $defaultValue;

    /**
     * @var boolean Flag telling whether search index extraction is applicable.
     */
    protected $isSearchable = false;

    /**
     * @var FieldSettings Custom properties which are specific to the field
     *                      type. Typically these properties are used to
     *                      configure behaviour of field types and normally set
     *                      in the FieldDefinition on ContentTypes
     */
    protected $fieldSettings;

    /**
     * The setting keys which are available on this field type.
     *
     * The key is the setting name, and the value is the default value for given
     * setting, set to null if no particular default should be set.
     *
     * @var array
     */
    protected $allowedSettings = array();

    /**
     * Validators which are supported for this field type.
     *
     * Key is the name of supported validators, value is an array of stored settings.
     *
     * @var array
     */
    protected $allowedValidators = array();

    /**
     * Value of field type.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Constructs field type object, initializing internal data structures.
     */
    public function __construct()
    {
        $this->fieldSettings = new FieldSettings( $this->allowedSettings );
    }

    /**
     * Does the field type support search.
     *
     * @return boolean
     */
    public function supportsSearch()
    {
        return $this->isSearchable;
    }

    /**
     * Returns the field type identifier.
     *
     * @return string
     */
    public function type()
    {
        return $this->fieldTypeString;
    }

    /**
     * Set $setting on field type.
     *
     * Allowed options are in {@link $allowedSettings}
     *
     * @param string $setting
     * @param mixed $value
     * @return void
     */
    public function setFieldSetting( $setting, $value )
    {
        $this->fieldSettings[$setting] = $value;
    }

    /**
     * Set all settings on field type.
     *
     * Useful to initialize field type from a field definition.
     *
     * @param array $values
     * @return void
     */
    public function initializeSettings( array $values )
    {
        $this->fieldSettings->exchangeArray( $values );
    }

    /**
     * Return a copy of the array of fieldSettings.
     *
     * @return array
     */
    public function getFieldTypeSettings()
    {
        return $this->fieldSettings->getArrayCopy();
    }

    /**
     * Keys of settings which are available on this fieldtype.
     * @return array
     */
    public function allowedSettings()
    {
        return array_keys( $this->allowedSettings );
    }

    /**
     * Return an array of allowed validators to operate on this field type.
     *
     * @return array
     */
    public function allowedValidators()
    {
        return $this->allowedValidators;
    }

    /**
     * Checks if value can be parsed.
     *
     * If the value actually can be parsed, the value is returned.
     *
     * @abstract
     * @throws ezp\Base\Exception\BadFieldTypeInput Thrown when $inputValue is not understood.
     * @param mixed $inputValue
     * @return mixed
     */
    abstract protected function canParseValue( $inputValue );

    /**
     * Sets the value of a field type.
     *
     * @abstract
     * @param $inputValue
     * @return void
     */
    abstract public function setValue( $inputValue );

    /**
     * Returns the value of a field type.
     *
     * If no value has yet been set, the default value of that field type is
     * returned.
     *
     * @return mixed
     */
    public function getValue()
    {
        if ( $this->value === null )
        {
            return $this->defaultValue;
        }
        return $this->value;
    }

    /**
     * Returns a handler, aka. a helper object which aids in the manipulation of
     * complex field type values.
     *
     * @abstract
     * @return void|ezp\Content\FieldType\Handler
     */
    abstract public function getHandler();

    /**
     * Method to populate the FieldValue struct for field types.
     *
     * This method is used by the business layer to populate the value object
     * for field type data.
     *
     * @internal
     * @abstract
     * @param \ezp\Persistence\Content\FieldValue $valueStruct The value struct which the field type data is packaged in for consumption by the storage engine.
     * @return void
     */
    abstract public function setFieldValue( FieldValue $valueStruct );

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @abstract
     * @return array
     */
    abstract protected function getSortInfo();

    /**
     * Returns the value of the field type in a format suitable for packing it
     * in a FieldValue.
     *
     * @abstract
     * @return array
     */
    abstract protected function getValueData();

    /**
     * Used by the FieldDefinition to populate the fieldConstraints field.
     *
     * If validator is not allowed for a given field type, no data from that
     * validator is populated to $constraints.
     *
     * @todo Consider separating out the fieldTypeConstraints into a sepsrate object, so that it could be passed, and not the whole FieldDefinition object.
     *
     * @abstract
     * @internal
     * @param FieldDefinition $fieldDefinition
     * @param \ezp\Content\FieldType\Validator $validator
     * @return void
     */
     public function fillConstraintsFromValidator( FieldDefinition $fieldDefinition, $validator )
     {
         if ( in_array( $validator->name(), $this->allowedValidators() ) )
         {
             $fieldDefinition->fieldTypeConstraints = array_merge( $fieldDefinition->fieldTypeConstraints, $validator->getValidatorConstraints() );
         }
     }

    /**
     * Factory method for creating field type object based on identifiers.
     *
     * @static
     * @throws \ezp\Base\Exception\MissingClass
     * @param string $type
     * @return \ezp\Content\FieldType
     */
    public static function create( $type )
    {
        $fieldTypeMap = Configuration::getInstance( 'content' )->get( 'fields', 'Type' );
        if ( !isset( $fieldTypeMap[$type] ) )
        {
            throw new MissingClass( $type, 'FieldType' );
        }
        return new $fieldTypeMap[$type];
    }
}
