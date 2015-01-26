<?php

namespace Drupal\vina_migrate\TruyenCuaTuiNet\Source;

use Drupal\vina_migrate\Base\Source\PaginationHTMLPageSource;
use Drupal\vina_migrate\Base\Source\PaginationIndexedPageSource;
use Symfony\Component\DomCrawler\Crawler;

class ChapterSource
{

    use PaginationHTMLPageSource,
        PaginationIndexedPageSource {
        PaginationIndexedPageSource::__construct as paginationIndexedPageConstructor;
        PaginationIndexedPageSource::isLastPage insteadof PaginationHTMLPageSource;
        PaginationIndexedPageSource::getPagePath insteadof PaginationHTMLPageSource;
    }

    public function __construct()
    {
        $this->basePath = 'http://truyencuatui.net';
        $this->landingPage = 'http://truyencuatui.net/truyen/kinh-van-hoa.html';
        $this->indexItemsQuery = '#danh-sach-chuong > a';
        $this->indexPagerItemsQuery = '.panel-primary .panel-body ul.pagination > li';
        $this->pageItemsQuery = '.panel-primary article';
        $this->pagerItemsQuery = 'ul.pagination > li';
        $this->paginationIndexedPageConstructor();
    }

    protected function convertNodeToArrayItem()
    {
        return function(Crawler $node) {
            $item = $node->first();

            return [
                'title'       => $item->first()->filter('h1')->first()->text(),
                'description' => $item->first()->filter('meta')->first()->attr('content'),
                'body'        => $item->filter('div.content')->first()->html(),
            ];
        };
    }

}
