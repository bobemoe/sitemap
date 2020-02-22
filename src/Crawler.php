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

    public function __construct($baseUrl=null){
        $this->observer = new CrawlObserver();
        $this->crawler = SpatieCrawler::create([
            RequestOptions::ALLOW_REDIRECTS => [
                'track_redirects' => true,
            ],
            RequestOptions::CONNECT_TIMEOUT => 10,
            RequestOptions::TIMEOUT => 10,
        ])
            //->setMaximumDepth(1)
            ->setCrawlObserver($this->observer)
        ;
        if($baseUrl){
            $this->crawler->setCrawlProfile(new CrawlInternalUrls($baseUrl));
        }
    }

    public function crawl($url){
        $this->crawler->startCrawling($url);
    }

    public function getResults(){
        return $this->observer->results;
    }

}
