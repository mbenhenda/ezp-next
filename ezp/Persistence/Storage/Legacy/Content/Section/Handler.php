<?php
/**
 * File containing the Section Handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Section;
use ezp\Persistence\Content\Section\Handler as BaseSectionHandler,
    ezp\Persistence\Content\Section;

/**
 * Section Handler
 */
class Handler implements BaseSectionHandler
{
    /**
     * Section Gateway
     *
     * @var \ezp\Persistence\Storage\Legacy\Content\Section\Gateway $sectionGateway
     */
    protected $sectionGateway;

    /**
     * Creates a new Section Handler
     *
     * @param \ezp\Persistence\Storage\Legacy\Content\Section\Gateway $sectionGateway
     */
    public function __construct( Gateway $sectionGateway  )
    {
        $this->sectionGateway = $sectionGateway;
    }

    /**
     * Creat a new section
     *
     * @param string $name
     * @param string $identifier
     * @return \ezp\Persistence\Content\Section
     */
    public function create( $name, $identifier )
    {
        $section = new Section();

        $section->name = $name;
        $section->identifier = $identifier;

        $section->id = $this->sectionGateway->insertSection( $name, $identifier );

        return $section;
    }

    /**
     * Update name and identifier of a section
     *
     * @param mixed $id
     * @param string $name
     * @param string $identifier
     */
    public function update( $id, $name, $identifier )
    {
        $this->sectionGateway->updateSection( $id, $name, $identifier );

        $section = new Section();
        $section->id = $id;
        $section->name = $name;
        $section->identifier = $identifier;

        return $section;
    }

    /**
     * Get section data
     *
     * @param mixed $id
     * @return \ezp\Persistence\Content\Section|null
     */
    public function load( $id )
    {
        $rows = $this->sectionGateway->loadSectionData( $id );

        if ( count( $rows ) < 1 )
        {
            throw new \RuntimeException( "Section with ID '{$id}' not found." );
        }
        return $this->createSectionFromArray( reset( $rows ) );
    }

    /**
     * Creates a Section from the given $data
     *
     * @param array $data
     * @return \ezp\Persistence\Content\Section
     */
    protected function createSectionFromArray( array $data )
    {
        $section = new Section();

        $section->id = (int)$data['id'];
        $section->name = $data['name'];
        $section->identifier = $data['identifier'];

        return $section;
    }

    /**
     * Delete a section
     *
     * Might throw an exception if the section is still associated with some
     * content objects. Make sure that no content objects are associated with
     * the section any more *before* calling this method.
     *
     * @param mixed $id
     */
    public function delete( $id )
    {
        $contentCount = $this->sectionGateway->countContentObjectsInSection( $id );

        if ( $contentCount > 0 )
        {
            throw new \RuntimeException(
               "Section with ID '{$id}' still has content assigned."
            );
        }
        $this->sectionGateway->deleteSection( $id );
    }

    /**
     * Assign section to single content object
     *
     * @param mixed $sectionId
     * @param mixed $contentId
     */
    public function assign( $sectionId, $contentId )
    {
        $this->sectionGateway->assignSectionToContent( $sectionId, $contentId );
    }
}
?>
