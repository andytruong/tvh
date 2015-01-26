<?php

namespace Drupal\vina_migrate\Base\Source;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * When we migrate items listed on multiple pages, but there's no pager. But
 * there's an index page, which list all links to pages.
 *
 * The index page maybe too big, provide may split them to pages with pager.
 */
trait PaginationIndexedPageSource
{

    protected $basePath;
    protected $landingPage;
    protected $index = [];
    protected $indexItemsQuery;
    protected $indexPagerItemsQuery;

    public function __construct()
    {
        $this->fillIndex();
    }

    protected function fillIndex()
    {
        $client = new Client();

        // Get links from first page
        $page = $client->request('GET', $this->landingPage);
        $this->index = $page->filter($this->indexItemsQuery)->each(function(Crawler $node) {
            return $node->attr('href');
        });

        // Go next page to get more links for index
        do {
            if (!$links = $this->fillIndexFollowPager($client, $page)) {
                break;
            }

            $this->index = array_merge($this->index, $links);
        } while (true);
    }

    protected function fillIndexFollowPager(Client $client, Crawler &$page)
    {
        $lastListItem = $page->filter($this->indexPagerItemsQuery)->last();
        $nextLink = $lastListItem->filter('a');
        if ($nextLink->count()) {
            $page = $client->click($nextLink->link());

            return $page->filter($this->indexItemsQuery)->each(function(Crawler $link) {
                    return $link->attr('href');
                });
        }
    }

    protected function isLastPage()
    {
        return $this->currentPage > count($this->index);
    }

    protected function getPagePath()
    {
        return $this->basePath . '/' . ltrim($this->index[$this->currentPage - 1], '/');
    }

}
