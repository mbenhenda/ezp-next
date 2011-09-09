<?php
/**
 * File containing the ezp\Io\BinaryStorage\Legacy class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Io\BinaryStorage;

use ezp\Io\BinaryStorage\Backend,
    ezp\Io\BinaryFile, ezp\Io\BinaryFileCreateStruct, ezp\Io\BinaryFileUpdateStruct, ezp\Io\ContentType,
    ezp\Io\BinaryStorage\Legacy\FileResourceAdapter,
    ezp\Base\Exception\InvalidArgumentValue,
    eZClusterFileHandler,
    DateTime,
    ezp\Base\Exception\NotFound, ezp\Io\Exception\PathExists;

/**
 * Legacy BinaryStorage handler, based on eZ Cluster
 *
 * Due to the legacy API, this handler has a few limitations:
 * - ctime is not really supported, and will always have the same value as mtime
 * - mtime can not be modified, and will always automatically be set depending on the server time upon each write operation
 */

class Legacy implements Backend
{
    public function __construct()
    {
        $this->clusterHandler = eZClusterFileHandler::instance();
    }

    /**
     * Creates a new BinaryFile based on the BinaryFileCreateStruct $file
     * @param BinaryFileCreateStruct $file
     *
     * @return BinaryFile The created BinaryFile object
     *
     * @throws \ezp\Base\Exception\PathExists If the target path already exists
     */
    public function create( BinaryFileCreateStruct $file )
    {
        if ( $this->exists( $file->path ) )
        {
            throw new PathExists( $file->path );
        }

        // @todo Build a path / scope mapper
        $scope = 'todo';
        $this->clusterHandler->fileStoreContents(
            $file->path,
            fread( $file->getInputStream(), $file->size ),
            (string)$file->contentType,
            $scope
        );

        return $this->load( $file->path );
    }

    /**
     * Deletes the file $path
     * @param string $path
     * @throws \ezp\Base\Exception\NotFound If the file doesn't exist
     */
    public function delete( $path )
    {
        if ( !$this->clusterHandler->fileExists( $path ) )
        {
            throw new NotFound( 'BinaryFile', $path );
        }

        $this->clusterHandler->fileDelete( $path );
    }

    /**
     * Updates the file identified by $path with data from $updateFile
     *
     * @param string $path
     * @param BinaryFileUpdateStruct $updateFile
     *
     * @return BinaryFile The updated BinaryFile
     *
     * @throws \ezp\Base\Exception\NotFound If the source path doesn't exist
     * @throws \ezp\Base\Exception\PathExists If the target path already exists
     */
    public function update( $path, BinaryFileUpdateStruct $updateFile )
    {
        if ( !$this->clusterHandler->fileExists( $path ) )
        {
            throw new NotFound( 'BinaryFile', $path );
        }

        // path
        if ( $updateFile->path !== null && $updateFile->path != $path )
        {
            if ( $this->clusterHandler->fileExists( $updateFile->path ) )
            {
                throw new PathExists( $updateFile->path );
            }
            $this->clusterHandler->fileMove( $path, $updateFile->path );

            // update the path we are working on
            $path = $updateFile->path;
        }

        $resource = $updateFile->getInputStream();
        if ( $resource !== null )
        {
            $binaryUpdateData = fread( $resource, $updateFile->size );
            $clusterFile = eZClusterFileHandler::instance( $path );
            $metaData = $clusterFile->metaData;
            $scope = isset( $metaData['scope'] ) ? $metaData['scope'] : false;
            $datatype = isset( $metaData['datatype'] ) ? $metaData['datatype'] : false;
            $clusterFile->storeContents( $binaryUpdateData, $scope, $datatype );
        }

        // mtime and ctime have no effect as mtime isn't modifiable, and ctime isn't really supported (=mtime)

        return $this->load( $path );
    }

    /**
     * Returns a file resource to the BinaryFile identified by $path
     * @param string $path
     * @return resource
     * @throws \ezp\Base\Exception\NotFound If the file doesn't exist
     */
    public function getFileResource( $path )
    {
        return $this->getFileResourceProvider()->getResource( $this->load( $path ) );
    }

    /**
     * Checks if a file with $path exists in the backend
     * @var string $path
     * @return bool
     */
    public function exists( $path )
    {
        return $this->clusterHandler->fileExists( $path );
    }

    /**
     * Loads the BinaryFile identified by $path
     * @param string $path
     * @return BinaryFile
     * @throws ezp\Base\Exception\NotFound When the requested $path doesn't exist
     */
    public function load( $path )
    {
        if ( !$this->exists( $path ) )
        {
            throw new NotFound( 'BinaryFile', $path );
        }

        $clusterFile = eZClusterFileHandler::instance( $path );

        $metaData = $clusterFile->metaData;

        $file = new BinaryFile();
        $file->path = $path;

        $file->mtime = new DateTime();
        $file->mtime->setTimestamp( $metaData['mtime'] );

        $file->ctime = $file->mtime;

        $file->size = $metaData['size'];

        // will only work with some ClusterFileHandlers (DB based ones, not with FS ones)
        if ( isset( $metaData['datatype'] ) )
        {
            list( $type, $subType ) = explode( '/', $metaData['datatype'] );
            $file->contentType = new ContentType( $type, $subType );
        }
        else
        {
            $file->contentType = ContentType::getFromPath( $path );
        }

        $file->uri = $file->path;

        return $file;
    }

    /**
     * Returns the contents of the BinaryFile identified by $path
     * @param string $path
     * @return BinaryContents
     * @throws ezp\Base\Exception\NotFound if the file couldn't be found
     */
    public function getFileContents( $path )
    {
        if ( !$this->clusterHandler->fileExists( $path ) )
        {
            throw new NotFound( 'BinaryFile', $path );
        }

        return $this->clusterHandler->fileFetchContents( $path );
    }

    /**
     * Returns the appropriate FileResourceProvider depending on the cluster handler in use
     * @return \ezp\Io\BinaryStorage\Legacy\FileResourceProvider
     */
    private function getFileResourceProvider()
    {
        if ( !isset( $this->fileResourceProvider ) )
        {
            $class = __CLASS__ . '\\FileResourceProvider\\' . get_class( $this->clusterHandler );
            if ( !class_exists( $class ) )
            {
                throw new \Exception( "FileResourceProvider $class couldn't be found" );
            }
            $this->fileResourceProvider = new $class;
        }

        return $this->fileResourceProvider;
    }

    /**
     * File resource provider
     * @see getFileResourceProvider
     */
    private $fileResourceProvider = null;

    /**
     * Cluster handler instance
     * @var eZClusterFileHandlerInterface
     */
    private $clusterHandler;
}
?>
