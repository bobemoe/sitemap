<?php
use \PHPUnit\Framework\TestCase;

use JHodges\Sitemap\Crawler;

class CrawlerTest extends TestCase{

    public function testCanCrawlSite(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080');
        $sitemap=$crawler->getResults();
        $this->assertSitemapContains($sitemap,[
            'http://localhost:8080/' => ['code' => 200 ],
            'http://localhost:8080/link1' => ['code' => 200 ],
            'http://localhost:8080/link2' => ['code' => 200 ],
            'http://localhost:8080/link3' => ['code' => 200 ],
            'http://localhost:8080/link4' => ['code' => 302 ],
            'http://localhost:8080/notExists' => ['code' => 404 ],
        ]);
    }

    public function testCanFollowRedirectToFound(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/redirectToFound');
        $sitemap=$crawler->getResults();
        $this->assertSitemapContains($sitemap,[
            'http://localhost:8080/redirectToFound' => ['code' => 302 ],
            'http://localhost:8080/' => ['code' => 200 ],
        ]);
    }

    public function testCanFollowRedirectToNotFound(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/redirectToNotFound');
        $sitemap=$crawler->getResults();
        $this->assertSitemapContains($sitemap,[
            'http://localhost:8080/redirectToNotFound' => ['code' => 302 ],
            'http://localhost:8080/notExists' => ['code' => 404 ],
        ]);
    }

    public function testCanFollowRedirectToRedirectToNotFound(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/redirectToRedirectToNotFound');
        $sitemap=$crawler->getResults();
        $this->assertSitemapContains($sitemap,[
            'http://localhost:8080/redirectToRedirectToNotFound' => ['code' => 302 ],
            'http://localhost:8080/redirectToNotFound' => ['code' => 302 ],
            'http://localhost:8080/notExists' => ['code' => 404 ],
        ]);
    }

    public function testCanFollowTwoRedirectsToSameLocation(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/twoRedirectsToSameLocation');
        $sitemap=$crawler->getResults();
        $this->assertSitemapContains($sitemap,[
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
        $this->assertSitemapContains($sitemap,[
            'http://localhost:8080/timeout' => ['code' => '???' ],
        ]);
    }

    public function testInternalServerError(){
        $crawler=new Crawler('http://localhost:8080');
        $crawler->crawl('http://localhost:8080/internalServerError');
        $sitemap=$crawler->getResults();
        $this->assertSitemapContains($sitemap,[
            'http://localhost:8080/internalServerError' => ['code' => 500 ],
        ]);
    }

    public function assertSitemapContains($sitemap, $contains){
        foreach($contains as $url=>$vals){
            $this->assertArrayHasKey($url, $sitemap, "$url not found in sitemap");
            foreach($vals as $k=>$v){
                $this->assertArrayHasKey($k, $sitemap[$url], "$url => $k not found in sitemap");
                $this->assertEquals($v, $sitemap[$url][$k], "$url => $k = $v not found in sitemap");
            }
        }
    }

}
