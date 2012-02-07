<?php
namespace eZ\Publish\Core\Repository\Values\User;
use eZ\Publish\API\Repository\Values\User\PolicyUpdateStruct as APIPolicyUpdateStruct;
use eZ\Publish\API\Repository\Values\User\Limitation;

/**
 * This class is used for updating a policy. The limitations of the policy are replaced
 * with those which are added in instances of this class
 */
class PolicyUpdateStruct extends APIPolicyUpdateStruct
{
    /**
     * List of limitations added to policy
     * @todo move to abstract class
     *
     * @var array
     */
    protected $limitations = array();

    /**
     *
     * adds a limitation to the policy - if a Limitation exists with the same identifer
     * the existing limitation is replaced
     * @param \eZ\Publish\API\Repository\Values\User\Limitation $limitation
     */
    public function addLimitation( /*Limitation*/ $limitation )
    {
        $limitationIdentifier = $limitation->getIdentifier();
        $this->limitations[$limitationIdentifier] = $limitation;
    }
}