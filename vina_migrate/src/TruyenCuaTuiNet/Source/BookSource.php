<?php

namespace Drupal\vina_migrate\TruyenCuaTuiNet\Source;

use Drupal\vina_migrate\Base\Source\PaginationHTMLPageSource;
use Symfony\Component\DomCrawler\Crawler;

class BookSource
{

    use PaginationHTMLPageSource {
        isLastPage as superIsLastpage;
    }

    public function __construct()
    {
        $this->path = 'http://truyencuatui.net/truyen-moi.html?page=:page';
        $this->pageItemsQuery = '.panel-primary div.truyen-inner';
        $this->pagerItemsQuery = 'ul.pagination > li';
    }

    protected function isLastPage()
    {
        if (null === ($return = $this->superIsLastpage())) {
            if ($this->pagerItems->filter('a')->count() > 1) {
                if (1 == $this->currentPage) {
                    return false;
                }
                return $this->pagerItems->last()->filter('.disabled')->count() ? true : false;
            }
        }
        return $return;
    }

    protected function convertNodeToArrayItem()
    {
        return function(Crawler $node) {
            return [
                'id'        => str_replace(['/truyen/', '.html'], '', $node->filter('a:nth-child(1)')->first()->attr('href')),
                'image'     => $node->filter('a:nth-child(1)')->attr('data-src'),
                'title'     => $node->filter('a:nth-child(1)')->children()->first()->text(),
                'author_id' => str_replace(['/tac-gia/', '.html'], '', $node->filter('.author a')->attr('href')),
            ];
        };
    }

}
