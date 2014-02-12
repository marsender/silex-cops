<?php
/*
 * This file is part of Silex Cops. Licensed under WTFPL
 *
 * (c) Mathieu Duplouy <mathieu.duplouy@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cops\Model;

use Cops\Model\Core;
use Cops\Model\BookFile\BookFileFactory;

/**
 * Book model class
 *
 * @author Mathieu Duplouy <mathieu.duplouy@gmail.com>
 */
class Book extends Common
{
    /**
     * Object ID
     * @var int
     */
    protected $id;

    /**
     * Publication date
     * @var string
     */
    protected $pubdate;

    /**
     * Title
     * @var string
     */
    protected $title;

    /**
     * Has cover
     * @var bool
     */
    protected $hasCover;

    /**
     * Data path
     * @var string
     */
    protected $path;

    /**
     * Rating
     * @var string
     */
    protected $rating;

    /**
     * Comment
     * @var string
     */
    protected $comment;

    /**
     * Serie index
     * @var string
     */
    protected $seriesIndex;

    /**
     * An Author object instance
     * @var \Cops\Model\Author
     */
    protected $_author;

    /**
     * A Cover object instance
     * @var \Cops\Model\Cover
     */
    protected $_cover;

    /**
     * A Serie object instance
     * @var \Cops\Model\Serie
     */
    protected $_serie;

    /**
     * A tag collection instance
     * @var \Cops\Model\Tag\Collection
     */
    protected $tags;

    /**
     * An array of file adapter instance
     * @var array
     */
    protected $_file = array();

    /**
     * Load book
     *
     * @param int $bookId
     *
     * @return \Cops\Model\Book
     */
    public function load($bookId)
    {
        $result = $this->getResource()->load($bookId);

        $this->setData($result);

        if (!empty($result['author_id'])) {
            $this->getAuthor()->setData(array(
                'id'   => $result['author_id'],
                'name' => $result['author_name'],
                'sort' => $result['author_sort'],
            ));
        }

        if (!empty($result['serie_id'])) {
            $this->getSerie()->setData(array(
                'id'   => $result['serie_id'],
                'name' => $result['serie_name'],
                'sort' => $result['serie_sort'],
            ));
        }

        // @TODO, change this
        $this->getModel('BookFile')->loadFromBook($this);

        return $this;
    }

    /**
     * Has cover
     *
     * @return bool
     */
    public function hasCover()
    {
        return (bool) $this->hasCover;
    }

    /**
     * Cover object getter
     *
     * @param string $storageDir
     *
     * @return Cover
     */
    public function getCover($storageDir = null)
    {
        if ($this->_cover === null) {
            $this->_cover = $this->getModel('Cover', array($this, $storageDir));
        }
        return $this->_cover;
    }

    /**
     * Serie object getter
     *
     * @return \Cops\Model\Serie
     */
    public function getSerie()
    {
        if (is_null($this->_serie)) {
            $this->_serie = $this->getModel('Serie');
        }
        return $this->_serie;
    }

    /**
     * Author object getter
     *
     * @return \Cops\Model\Author
     */
    public function getAuthor()
    {
        if (is_null($this->_author)) {
            $this->_author = $this->getModel('Author');
        }
        return $this->_author;
    }

    /**
     * File adapter getter
     *
     * @return \Cops\Model\BookFile\BookFileInterface
     */
    public function getFile($fileType = BookFileFactory::TYPE_EPUB)
    {
        if (!isset($this->_file[$fileType])) {
            $this->_file[$fileType] = $this->getModel('BookFile\\BookFileFactory', $fileType)
                ->getInstance();
        }
        return $this->_file[$fileType];
    }

    /**
     * Get all files adapter
     *
     * @return array An array of fileInterface instance
     */
    public function getFiles()
    {
        return $this->_file;
    }

    /**
     * Tag getter
     *
     * @return Collection A tag collection (can be empty)
     */
    public function getTags()
    {
        return $this->getModel('Tag')
            ->getCollection()
            ->getByBookId($this->getId());
    }

    /**
     * Update book author
     *
     * @param array $author
     * @param int   $bookId
     *
     * @return bool
     */
    public function updateAuthor($author, $bookId = null)
    {
        if ($bookId === null) {
            $bookId = $this->getId();
        }
        return $this->getResource()->updateAuthor($bookId, explode('&', $author));
    }

    /**
     * Update book title
     *
     * @param string $title
     * @param int    $bookId
     *
     * @return bool
     */
    public function updateTitle($title, $bookId = null)
    {
        if ($bookId === null) {
            $bookId = $this->getId();
        }
        return $this->getResource()->updateTitle($bookId, $title);
    }

    /**
     * Empty properties on clone
     */
    public function __clone()
    {
        $this->id          = null;
        $this->pubdate     = null;
        $this->hasCover    = null;
        $this->path        = null;
        $this->rating      = null;
        $this->comment     = null;
        $this->seriesIndex = null;
        $this->_serie      = null;
        $this->_author     = null;
        $this->_cover      = null;
        $this->_file       = array();
        parent::__clone();
    }
}
