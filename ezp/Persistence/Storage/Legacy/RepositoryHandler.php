<?php
/**
 * File containing the RepositoryHandler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy;
use ezp\Persistence\Repository\Handler as HandlerInterface,
    ezp\Persistence\Storage\Legacy\Content,
    ezp\Persistence\Storage\Legacy\Content\Type,
    ezp\Persistence\Storage\Legacy\Content\Location\Handler as LocationHandler,
    ezp\Persistence\Storage\Legacy\User,
    ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry;

/**
 * The repository handler for the legacy storage engine
 *
 * @todo If possible, the handler should not receive the DSN but the database
 *       connection instead, so that the implementation becomes fully testable.
 */
class RepositoryHandler implements HandlerInterface
{
    /**
     * Content handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Handler
     */
    protected $contentHandler;

    /**
     * Field value converter registry
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
     */
    protected $fieldValueConverterRegistry;

    /**
     * Storage registry
     *
     * @var Content\StorageRegistry
     */
    protected $storageRegistry;

    /**
     * Content type handler
     *
     * @var Content\Type\Handler
     */
    protected $contentTypeHandler;

    /**
     * Location handler
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Location\Handler
     */
    protected $locationHandler;

    /**
     * User handler
     *
     * @var User\Handler
     */
    protected $userHandler;

    /**
     * Section handler
     *
     * @var mixed
     */
    protected $sectionHandler;

    /**
     * Creates a new repository handler.
     *
     * The $dsn is a data source name as expected by the Zeta Components
     * database component. The format is:
     *
     *     <database>://<user>:<password>@<host>/<database>
     *
     * For example
     *
     *     mysql://root:secret@localhost/ezp
     *
     * for a MySQL database connection or
     *
     *    sqlite://:memory:
     *
     * for an SQLite in-memory database.
     *
     * For further information refer to
     * {@see http://incubator.apache.org/zetacomponents/documentation/trunk/Database/tutorial.html#handler-usage}
     *
     * @param string $dsn
     */
    public function __construct( $dsn )
    {
        $this->dbHandler = new EzcDbHandler( \ezcDbFactory::create( $dsn ) );
    }

    /**
     * @return \ezp\Persistence\Content\Handler
     */
    public function contentHandler()
    {
        if ( !isset( $this->contentHandler ) )
        {
            $this->contentHandler = new Content\Handler(
                new Content\Gateway\EzcDatabase( $this->dbHandler ),
                $this->locationHandler(),
                new Content\Mapper( $this->getFieldValueConverterRegistry() ),
                new Content\StorageRegistry( $this->getStorageRegistry() )
            );
        }
        return $this->contentHandler;
    }

    /**
     * Returns the field value converter registry
     *
     * @return \ezp\Persistence\Storage\Legacy\Content\FieldValue\Converter\Registry
     */
    public function getFieldValueConverterRegistry()
    {
        if ( !isset( $this->fieldValueConverterRegistry ) )
        {
            $this->fieldValueConverterRegistry =
                new Registry();
        }
        return $this->fieldValueConverterRegistry;
    }

    /**
     * Returns the storage registry
     *
     * @return Content\StorageRegistry
     */
    public function getStorageRegistry()
    {
        if ( !isset( $this->storageRegistry ) )
        {
            $this->storageRegistry = new Content\StorageRegistry();
        }
        return $this->storageRegistry;
    }

    /**
     * @return \ezp\Persistence\Content\Type\Handler
     */
    public function contentTypeHandler()
    {
        if ( !isset( $this->contentTypeHandler ) )
        {
            $this->contentTypeHandler = new Type\Handler(
                new Type\Gateway\EzcDatabase( $this->dbHandler ),
                new Type\Mapper()
            );
        }
        return $this->contentTypeHandler;
    }

    /**
     * @return \ezp\Persistence\Content\Location\Handler
     */
    public function locationHandler()
    {
        if ( !isset( $this->locationHandler ) )
        {
            $this->locationHandler = new LocationHandler(
                new Content\Location\Gateway\EzcDatabase( $this->dbHandler )
            );
        }
        return $this->locationHandler;
    }

    /**
     * @return \ezp\Persistence\User\Handler
     */
    public function userHandler()
    {
        if ( !isset( $this->userHandler ) )
        {
            $this->userHandler = new User\Handler(
                new User\Gateway\EzcDatabase( $this->dbHandler ),
                new User\Role\Gateway\EzcDatabase( $this->dbHandler )
            );
        }
        return $this->userHandler;
    }

    /**
     * @return \ezp\Persistence\Content\Section\Handler
     */
    public function sectionHandler()
    {
        throw new RuntimeException( 'Not implemented, yet.' );
    }

    /**
     */
    public function beginTransaction()
    {
        $this->dbHandler->beginTransaction();
    }

    /**
     */
    public function commit()
    {
        $this->dbHandler->commit();
    }

    /**
     */
    public function rollback()
    {
        $this->dbHandler->rollback();
    }
}
?>
