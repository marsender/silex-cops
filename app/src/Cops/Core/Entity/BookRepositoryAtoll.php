<?php
/*
 * This file is part of Silex Cops. Licensed under WTFPL
 *
 * (c) Mathieu Duplouy <mathieu.duplouy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cops\Core\Entity;

use Cops\Core\AbstractRepository;

/**
 * Atoll version of book repository in order to surchage search
 * @author Didier Corbière <contact@atoll-digital-library.org>
 */
class BookRepositoryAtoll extends BookRepository
{
    /**
     * Find by keyword using author and title tables instread of path
     *
     * @param  array  $keywords
     *
     * @return array
     */
    public function findByKeyword(array $keywords)
    {
        $qb = $this->getBaseSelect()
            ->addSelect('main.id')
            ->leftJoin('main', 'books_authors_link', 'bal',    'bal.book = main.id')
            ->leftJoin('main', 'authors',            'author', 'author.id = bal.author')
            ->orderBy('serie_name')
            ->addOrderBy('series_index')
            ->addOrderBy('title')
            ->groupBy('main.id')
            ->resetQueryParts(array('where'));

        // Build the where clause
        $andTitle  = $qb->expr()->andX();
        $andAuthor  = $qb->expr()->andX();
        $andSerie = $qb->expr()->andX();

        foreach ($keywords as $keyword) {
            $andTitle->add(
                $qb->expr()->like('main.title', $this->getConnection()->quote('%'.$keyword.'%'))
            );
            $andAuthor->add(
                $qb->expr()->like('author.name', $this->getConnection()->quote('%'.$keyword.'%'))
            );
            $andSerie->add(
                $qb->expr()->like('serie.sort', $this->getConnection()->quote('%'.$keyword.'%'))
            );
        }

        $qb->orWhere($andTitle, $andAuthor, $andSerie); // $andPath

         return $this->paginate($qb, array('select', 'orderBy'))
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);
    }
}
