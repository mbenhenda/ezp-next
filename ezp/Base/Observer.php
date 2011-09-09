<?php
/**
 * Interface for observer, extended with support for certain events.
 * $event = 'update' means basically "updated" just as in normal observer code.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Base;

/**
 * Interface for Observers
 *
 */
interface Observer// extends \SplObserver
{
    /**
     * Called when subject has been updated
     *
     * @param Observable $subject
     * @param string $event
     * @param array|null $arguments
     * @return Observer
     */
    public function update( Observable $subject, $event = 'update', array $arguments = null );
}
