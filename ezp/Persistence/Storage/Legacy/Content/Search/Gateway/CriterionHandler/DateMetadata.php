<?php
/**
 * File containing the EzcDatabase date metadata criterion handler class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Persistence\Storage\Legacy\Content\Search\Gateway\CriterionHandler;
use ezp\Persistence\Storage\Legacy\Content\Search\Gateway\CriterionHandler,
    ezp\Persistence\Storage\Legacy\Content\Search\Gateway\CriteriaConverter,
    ezp\Persistence\Content\Criterion;

/**
 * Date metadata criterion handler
 */
class DateMetadata extends CriterionHandler
{
    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param Criterion $criterion
     * @return bool
     */
    public function accept( Criterion $criterion )
    {
        return $criterion instanceof Criterion\DateMetadata;
    }

    /**
     * Check if this criterion handler accepts to handle the given criterion.
     *
     * @param CriteriaConverter $converter
     * @param \ezcQuerySelect $query
     * @param Criterion $criterion
     * @return \ezcQueryExpression
     */
    public function handle( CriteriaConverter $converter, \ezcQuerySelect $query, Criterion $criterion )
    {
        $column = $criterion->target === Criterion\DateMetadata::MODIFIED ? 'modified' : 'published';

        switch ( $criterion->operator )
        {
            case Criterion\Operator::IN:
                return $query->expr->in( $column, $criterion->value );

            case Criterion\Operator::BETWEEN:
                return $query->expr->between(
                    $column,
                    $query->bindValue( $criterion->value[0] ),
                    $query->bindValue( $criterion->value[1] )
                );

            case Criterion\Operator::EQ:
            case Criterion\Operator::GT:
            case Criterion\Operator::GTE:
            case Criterion\Operator::LT:
            case Criterion\Operator::LTE:
                $operatorFunction = $this->comparatorMap[$criterion->operator];
                return $query->expr->$operatorFunction(
                    $column,
                    $query->bindValue( reset( $criterion->value ) )
                );

            default:
                throw new \RuntimeException( 'Unknown operator.' );
        }

    }
}

