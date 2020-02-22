<?php
use \PHPUnit\Framework\TestCase;

use JHodges\Sitemap\Crawler;

class CrawlerTest extends TestCase{

    public function testCanCrawlSite(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/' => ['code' => 200 ],
            'http://localhost:8080/link1' => ['code' => 200 ],
            'http://localhost:8080/link2' => ['code' => 200 ],
            'http://localhost:8080/link3' => ['code' => 200 ],
            'http://localhost:8080/link4' => ['code' => 302 ],
            'http://localhost:8080/notExists' => ['code' => 404 ],
        ]);
    }

    public function testCollectsAllFoundOnUrls(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/page1');
        $crawler->crawl('http://localhost:8080/page4'); // this ensures the order or results for the URL tracking test 3PARTS.
        $sitemap=$crawler->getResults();
        print_r($sitemap);
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/page1' => ['code' => 200 , 'foundOn' => [
                'http://localhost:8080/page1' => 1,
                'http://localhost:8080/page2' => 1,
                'http://localhost:8080/page3' => 1,
            ]],
            'http://localhost:8080/page2' => ['code' => 200 , 'foundOn' => [
                'http://localhost:8080/page1' => 1,
                'http://localhost:8080/page2' => 1,
                'http://localhost:8080/page3' => 1,
            ]],
            'http://localhost:8080/page3' => ['code' => 200 , 'foundOn' => [
                'http://localhost:8080/page1' => 1,
                'http://localhost:8080/page2' => 1,
                'http://localhost:8080/page3' => 1,
            ]],
            'http://localhost:8080/notFound1' => ['code' => 404 , 'foundOn' => [
                'http://localhost:8080/page1' => 1,
                'http://localhost:8080/page2' => 1,
                'http://localhost:8080/page3' => 1,
            ]],
            'http://localhost:8080/notFound2' => ['code' => 404 , 'foundOn' => [
                'http://localhost:8080/page1' => 1,
                'http://localhost:8080/page2' => 1,
                'http://localhost:8080/page3' => 1,
                'http://localhost:8080/page4' => 1,
            ]],
            'http://localhost:8080/redirectToNotFound' => ['code' => 302 , 'foundOn' => [
                'http://localhost:8080/page1' => 1,
                'http://localhost:8080/page2' => 1,
                'http://localhost:8080/page3' => 1,
                'http://localhost:8080/page4' => 1,
            ]],
            'http://localhost:8080/redirectToRedirectToNotFound' => ['code' => 302 , 'foundOn' => [
                'http://localhost:8080/page1' => 1,
                'http://localhost:8080/page2' => 1,
                'http://localhost:8080/page3' => 1,
                'http://localhost:8080/page4' => 1,
            ]],

        ]);
    }

    public function testCanFollowRedirectToFound(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/redirectToFound');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/redirectToFound' => ['code' => 302 ],
            'http://localhost:8080/' => ['code' => 200 ],
        ]);
    }

    public function testCanFollowRedirectToNotFound(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/redirectToNotFound');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/redirectToNotFound' => ['code' => 302 ],
            'http://localhost:8080/notFound2' => ['code' => 404 ],
        ]);
    }

    public function testCanFollowRedirectToRedirectToNotFound(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/redirectToRedirectToNotFound');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/redirectToRedirectToNotFound' => ['code' => 302 ],
            'http://localhost:8080/redirectToNotFound' => ['code' => 302 ],
            'http://localhost:8080/notFound2' => ['code' => 404 ],
        ]);
    }

    public function testCanFollowTwoRedirectsToSameLocation(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/twoRedirectsToSameLocation');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/twoRedirectsToSameLocation' => ['code' => 200 ],
            'http://localhost:8080/redirect1' => ['code' => 302 ],
            'http://localhost:8080/redirect2' => ['code' => 302 ],
            'http://localhost:8080/link1' => ['code' => 200 ],
        ]);
    }

    public function testTimeout(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/timeout');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/timeout' => ['code' => '' ],
        ]);
    }

    public function testInternalServerError(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/internalServerError');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/internalServerError' => ['code' => 500 ],
        ]);
    }

    public function assertTreeContains($haystack, $contains, $crumbs=''){
        foreach($contains as $k=>$v){
            $this->assertArrayHasKey($k, $haystack, $crumbs);
            if(is_array($v)){
                $this->assertTreeContains($haystack[$k], $v, $crumbs.' => '.$k);
            }else{
                $this->assertEquals($v, $haystack[$k], $crumbs.' => '.$k);
            }
        }
    }

}
