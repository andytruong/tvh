<?php

namespace Drupal\vina_migrate\Base\Source;

use Closure;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Items to be migrated listed on multiple pages. A simple pager is provided
 * for navigation.
 *
 * Example: Default /node page of Drupal.
 */
trait PaginationHTMLPageSource
{

    protected $currentPage = 1;
    protected $currentItem = 0;
    protected $currentPageItems = [];
    protected $pageItemsQuery;
    protected $pagerItemsQuery;

    /** @var Crawler */
    protected $pagerItems;

    public function computeCount()
    {
        return -1;
    }

    public function performRewind()
    {
        $this->currentPage = 1;
        $this->currentItem = 0;
    }

    public function getNextRow()
    {
        $this->flipPage();
        if (isset($this->currentPageItems[$this->currentItem])) {
            return $this->currentPageItems[$this->currentItem++];
        }
    }

    protected function flipPage()
    {
        // Items in page not used all
        if ($this->currentItem < count($this->currentPageItems)) {
            return;
        }

        // Last page, can not flip
        if ($this->isLastPage()) {
            return;
        }

        if ($items = $this->loadPageItems()) {
            $this->currentPage++;
            $this->currentItem = 0;
            $this->currentPageItems = $items;
        }
    }

    protected function isLastPage()
    {
        if (1 === $this->currentPage) {
            return false;
        }

        if (empty($this->pagerItems) || !($this->pagerItems instanceof Crawler)) {
            return true;
        }

        if ($this->pagerItems->filter('a')->count() > 1) {
            if (1 == $this->currentPage) {
                return false;
            }
            return $this->pagerItems->last()->filter('.disabled')->count() ? true : false;
        }
    }

    protected function getClient()
    {
        return new Client();
    }

    protected function getPagePath()
    {
        return str_replace(':page', $this->currentPage, $this->path);
    }

    protected function loadPageItems()
    {
        $path = $this->getPagePath();
        $page = $this->getClient()->request('GET', $path);

        if (null !== $this->pagerItemsQuery) {
            $this->pagerItems = $page->filter($this->pagerItemsQuery);
        }

        return $page->filter($this->pageItemsQuery)->each($this->convertNodeToArrayItem());
    }

    /**
     * @return Closure
     */
    abstract protected function convertNodeToArrayItem();
}
