<?php
namespace JHodges\Sitemap;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use Spatie\Crawler\Crawler as SpatieCrawler;
use Spatie\Crawler\CrawlUrl;
use Spatie\Crawler\CrawlInternalUrls;

class Crawler{

    private $observer;
    private $crawler;

    public function __construct($reqOps=[]){
        $this->crawler = SpatieCrawler::create(array_merge($reqOps, [
            RequestOptions::ALLOW_REDIRECTS => [
                'track_redirects' => true,
            ],
        ]));

        $this->observer = new CrawlObserver();
        $this->crawler->setCrawlObserver($this->observer);
    }

    public function crawl($url){
        $this->crawler->startCrawling($url);
    }

    public function getResults(){
        return $this->observer->results;
    }

}
