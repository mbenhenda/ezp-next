<?php
/**
 * File containing the eZ\Publish\MVC\SiteAccess\Matcher\HostText class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\MVC\SiteAccess\Matcher;

use eZ\Publish\MVC\SiteAccess\Matcher;

class HostText extends Regex implements Matcher
{
    /**
     * Constructor.
     *
     * @param array $URIElements Elements of the URI as parsed by parse_url().
     * @param array $siteAccessesConfiguration SiteAccesses configuration.
     */
    public function __construct( array $URIElements, array $siteAccessesConfiguration )
    {
        parent::__construct(
            $URIElements["host"],
            "^" .
            ( isset( $siteAccessesConfiguration["prefix"] ) ? preg_quote( $siteAccessesConfiguration["prefix"], "@" ) : "" ) .
            "(\w+)" .
            ( isset( $siteAccessesConfiguration["suffix"] ) ? preg_quote( $siteAccessesConfiguration["suffix"], "@" ) : "" ) .
            '$',
            1
        );
    }

    public function getName()
    {
        return 'host:text';
    }
}
