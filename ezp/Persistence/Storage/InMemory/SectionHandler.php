<?php
/**
 * File containing the SectionHandler implementation
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 *
 */

namespace ezp\Persistence\Storage\InMemory;
use ezp\Persistence\Content\Section\Handler as SectionHandlerInterface,
    RuntimeException;

/**
 * @see ezp\Persistence\Content\Section\Handler
 */
class SectionHandler implements SectionHandlerInterface
{
    /**
     * @var RepositoryHandler
     */
    protected $handler;

    /**
     * @var Backend
     */
    protected $backend;

    /**
     * Setups current handler instance with reference to RepositoryHandler object that created it.
     *
     * @param RepositoryHandler $handler
     * @param Backend $backend The storage engine backend
     */
    public function __construct( RepositoryHandler $handler, Backend $backend )
    {
        $this->handler = $handler;
        $this->backend = $backend;
    }

    /**
     * @see ezp\Persistence\Content\Section\Handler
     */
    public function create( $name, $identifier )
    {
        return $this->backend->create(
            'Content\\Section',
            array(
                'name' => $name,
                'identifier' => $identifier
            )
        );
    }

    /**
     * @see ezp\Persistence\Content\Section\Handler
     */
    public function update( $id, $name, $identifier )
    {
        $this->backend->update(
            'Content\\Section',
            $id,
            array(
                'id' => $id,
                'name' => $name,
                'identifier' => $identifier
            )
        );
    }

    /**
     * @see ezp\Persistence\Content\Section\Handler
     */
    public function load( $id )
    {
        return $this->backend->load( 'Content\\Section', $id );
    }

    /**
     * @see ezp\Persistence\Content\Section\Handler
     */
    public function delete( $id )
    {
        $this->backend->delete( 'Content\\Section', $id );
    }

    /**
     * @see ezp\Persistence\Content\Section\Handler
     */
    public function assign( $sectionId, $contentId )
    {
        // @todo Depends on working SubTree Criterion implementation.
        throw new RuntimeException( '@TODO: Implement' );
    }
}
?>
