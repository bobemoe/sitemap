<?php
use \PHPUnit\Framework\TestCase;

use JHodges\Sitemap\Crawler;

class CrawlerTest extends TestCase{

    public function testCrawl(){
        $crawler=new Crawler();
        $sitemap=$crawler->crawl('http://jhodges.co.uk');
        print_r($sitemap);
        return $sitemap;
    }

    /**
    * @depends testCrawl
    */
    public function testBrokenLinks($sitemap){
    }

}
