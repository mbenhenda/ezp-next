<?php
/**
 * File containing the User Handler interface
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\User;
use ezp\Persistence\User,
    ezp\Persistence\User\Role,
    ezp\Persistence\User\RoleUpdateStruct,
    ezp\Persistence\User\Policy;

/**
 * Storage Engine handler for user module
 *
 */
interface Handler
{

    /**
     * Create a user
     *
     * The User struct used to create the user will contain an ID which is used
     * to reference the user.
     *
     * @param \ezp\Persistence\User $user
     * @return \ezp\Persistence\User
     */
    public function create( User $user );

    /**
     * Load user with user ID.
     *
     * @param mixed $userId
     * @return \ezp\Persistence\User
     */
    public function load( $userId );

    /**
     * Update the user information specified by the user struct
     *
     * @param \ezp\Persistence\User $user
     */
    public function update( User $user );

    /**
     * Delete user with the given ID.
     *
     * @param mixed $userId
     * @todo Throw on missing user?
     */
    public function delete( $userId );

    /**
     * Create new role
     *
     * @param \ezp\Persistence\User\Role $role
     * @return \ezp\Persistence\User\Role
     */
    public function createRole( Role $role );

    /**
     * Load a specified role by id
     *
     * @param mixed $roleId
     * @return \ezp\Persistence\User\Role
     * @throws \ezp\Base\Exception\NotFound If role is not found
     */
    public function loadRole( $roleId );

    /**
     * Load roles assigned to a user/group (not including inherited roles)
     *
     * @param mixed $groupId
     * @return \ezp\Persistence\User\Role[]
     */
    public function loadRolesByGroupId( $groupId );

    /**
     * Update role
     *
     * @param \ezp\Persistence\User\RoleUpdateStruct $role
     */
    public function updateRole( RoleUpdateStruct $role );

    /**
     * Delete the specified role
     *
     * @param mixed $roleId
     */
    public function deleteRole( $roleId );

    /**
     * Adds a policy to a role
     *
     * @param mixed $roleId
     * @param \ezp\Persistence\User\Policy $policy
     * @return \ezp\Persistence\User\Policy
     * @todo Throw on invalid Role Id?
     */
    public function addPolicy( $roleId, Policy $policy );

    /**
     * Update a policy
     *
     * Replaces limitations values with new values.
     *
     * @param \ezp\Persistence\User\Policy $policy
     */
    public function updatePolicy( Policy $policy );

    /**
     * Removes a policy from a role
     *
     * @param mixed $roleId
     * @param mixed $policyId
     * @return void
     * @todo Throw exception on missing role / policy?
     */
    public function removePolicy( $roleId, $policyId );

    /**
     * Returns the user policies associated with the user (including inherited policies from user groups)
     *
     * @param mixed $userId
     * @return \ezp\Persistence\User\Policy[]
     */
    public function loadPoliciesByUserId( $userId );

    /**
     * Assign role to user group with given limitation
     *
     * The limitation array may look like:
     * <code>
     *  array(
     *      'Subtree' => array(
     *          '/1/2/',
     *          '/1/4/',
     *      ),
     *      'Foo' => array( 'Bar' ),
     *      …
     *  )
     * </code>
     *
     * Where the keys are the limitation identifiers, and the respective values
     * are an array of limitation values. The limitation parameter is optional.
     *
     * @todo It has been discussed to not support assigning roles with limitations, as it is kind of flawed in eZ Publish
     *       Hence you would simplify the design and reduce future bugs by forcing use of policy limitations instead.
     * @param mixed $groupId The group Id to assign the role to.
     *                       In Legacy storage engine this is the content object id of the group to assign to.
     *                       Assigning to a user is not supported, only un-assigning is supported for bc.
     * @param mixed $roleId
     * @param array $limitation
     */
    public function assignRole( $groupId, $roleId, array $limitation = null );

    /**
     * Un-assign a role
     *
     * @param mixed $groupId The group / user Id to un-assign a role from
     * @param mixed $roleId
     */
    public function unAssignRole( $groupId, $roleId );
}
?>
