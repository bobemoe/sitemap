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

    public function Crawl($url){
        $observer=new CrawlObserver();

        SpatieCrawler::create([
            RequestOptions::ALLOW_REDIRECTS => [
                'track_redirects' => true,
            ]
        ])
            //->setMaximumDepth(1)
            ->setCrawlObserver($observer)
            ->setCrawlProfile(new CrawlInternalUrls($url))
            //->addToCrawlQueue( CrawlUrl::create(new Uri('https://hudevad.com/en/')) )
            ->startCrawling($url)
        ;
        return $observer->results;
    }

}
