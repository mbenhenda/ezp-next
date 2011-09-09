<?php
/**
 * File containing Policy object
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\User;
use ezp\Base\Model,
    ezp\Base\Proxy,
    ezp\Base\Collection\Type as TypeCollection,
    ezp\Base\Exception\InvalidArgumentType,
    ezp\Persistence\User\Policy as PolicyValue,
    ezp\User\Role;

/**
 * This class represents a Policy item
 *
 * @property-read mixed $id
 * @property string $module
 * @property string $function
 * @property array|string $limitations
 * @property-read \ezp\User\Role|null $role
 */
class Policy extends Model
{
    /**
     * @var array Readable of properties on this object
     */
    protected $readWriteProperties = array(
        'id' => false,
        'module' => true,
        'function' => true,
        'limitations' => true,
        'roleId' => false,
    );

    /**
     * @var array Dynamic properties on this object
     */
    protected $dynamicProperties = array(
        'role' => false,
    );

    /**
     * @var \ezp\User\Role
     */
    protected $role;

    /**
     * Creates and setups User object
     *
     * @param \ezp\User\Role $role
     */
    public function __construct( $role )
    {
        if ( !$role instanceof Role && !$role instanceof Proxy )
            throw new InvalidArgumentType( '$role', 'ezp\\User\\Role, ezp\\Base\\Proxy', $role );

        $this->properties = new PolicyValue( array( 'roleId' => $role->id ) );
        $this->role = $role;
    }

    /**
     * @return \ezp\User\Role
     */
    protected function getRole()
    {
        if ( $this->role instanceof Proxy )
            $this->role = $this->role->load();

        $this->role;
    }
}
