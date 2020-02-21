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

    public function __construct($baseUrl){
        $this->observer = new CrawlObserver();
        $this->crawler = SpatieCrawler::create([
            RequestOptions::ALLOW_REDIRECTS => [
                'track_redirects' => true,
            ]
        ])
            //->setMaximumDepth(1)
            ->setCrawlProfile(new CrawlInternalUrls($baseUrl))
            ->setCrawlObserver($this->observer)
        ;
    }

    public function crawl($url){
        $this->crawler->startCrawling($url);
    }

    public function getResults(){
        return $this->observer->results;
    }

}
