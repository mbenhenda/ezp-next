<?php
/**
 * File containing the ezp\Persistence\Content\Query\SortClauseTarget class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Content\Query\SortClause\Target;

use ezp\Persistence\Content\Query\SortClause\Target as SortClauseTarget;

/**
 * Struct that stores extra target informations for a SortClause object
 */
class Field extends SortClauseTarget
{
    public function __construct( $typeIdentifier, $fieldIdentifier )
    {
        $this->typeIdentifier = $typeIdentifier;
        $this->fieldIdentifier = $fieldIdentifier;
    }

    public $typeIdentifier;
    public $fieldIdentifier;
}
?>