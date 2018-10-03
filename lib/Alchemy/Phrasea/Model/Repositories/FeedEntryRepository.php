<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2014 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Model\Repositories;

use Alchemy\Phrasea\Model\Entities\Feed;
use Alchemy\Phrasea\Model\Entities\FeedEntry;
use Doctrine\ORM\EntityRepository;

/**
 * FeedEntryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FeedEntryRepository extends EntityRepository
{
    /**
     * Returns a collection of FeedEntry from given feeds, limited to $how_many results, starting with $offset_start
     *
     * @param array   $feeds
     * @param integer $offset_start
     * @param integer $how_many
     *
     * @return FeedEntry[]
     */
    public function findByFeeds($feeds, $offset_start = null, $how_many = null)
    {
        $dql = 'SELECT f FROM Phraseanet:FeedEntry f
                WHERE f.feed IN (:feeds) order by f.updatedOn DESC';

        $query = $this->_em->createQuery($dql);
        $query->setParameter('feeds', $feeds);

        if (null !== $offset_start && 0 !== $offset_start) {
            $query->setFirstResult($offset_start);
        }
        if (null !== $how_many) {
            $query->setMaxResults($how_many);
        }

        return $query->getResult();
    }

    /**
     * @param Feed[]|array $feeds List of feeds instance or feed ids to
     * @return int
     */
    public function countByFeeds($feeds)
    {
        $builder = $this->createQueryBuilder('fe');
        $builder
            ->select($builder->expr()->count('fe'))
            ->where($builder->expr()->in('fe.feed', ':feeds'))
            ->setParameter('feeds', $feeds);

        return $builder->getQuery()->getSingleScalarResult();
    }
}
