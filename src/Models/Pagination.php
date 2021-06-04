<?php

namespace Guesl\Query\Models;

/**
 * Class Pagination
 * @package Guesl\Query\Models
 */
class Pagination
{
    const DEFAULT_PAGE_SIZE = 20;

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $pageSize;

    /**
     * Pagination constructor.
     *
     * @param int $page
     * @param int|null $pageSize
     */
    public function __construct(int $page, int $pageSize = null)
    {
        $this->page = $page;
        $this->pageSize = $pageSize ?? static::DEFAULT_PAGE_SIZE;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return Pagination
     */
    public function setPage(int $page): Pagination
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize ? $this->pageSize : self::DEFAULT_PAGE_SIZE;
    }

    /**
     * @param int $pageSize
     * @return Pagination
     */
    public function setPageSize(int $pageSize): Pagination
    {
        $this->pageSize = $pageSize;
        return $this;
    }
}
