<?php
use \PHPUnit\Framework\TestCase;

use JHodges\Sitemap\Crawler;
use GuzzleHttp\RequestOptions;

class CrawlerTest extends TestCase{

    public function testFullSite(){
        $crawler=new Crawler([RequestOptions::CONNECT_TIMEOUT => 3, RequestOptions::TIMEOUT => 3]);
        $crawler->crawl('http://localhost:8080/');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://example.com/' => ['code' => 200],
            'http://localhost:8080/deeplink1' => ['code' => 200],
            'http://localhost:8080/deeplink2' => ['code' => 200],
            'http://localhost:8080/deeplink3' => ['code' => 200],
            'http://localhost:8080/externalLink' => ['code' => 200],
            'http://localhost:8080/found' => ['code' => 200],
            'http://localhost:8080/interlinked1' => ['code' => 200],
            'http://localhost:8080/interlinked2' => ['code' => 200],
            'http://localhost:8080/interlinked3' => ['code' => 200],
            'http://localhost:8080/internalServerError' => ['code' => 500],
            'http://localhost:8080/invalidStatusCode' => ['code' => '---'],
            'http://localhost:8080/notFound' => ['code' => 404],
            'http://localhost:8080/redirect1' => ['code' => 302],
            'http://localhost:8080/redirect2' => ['code' => 302],
            'http://localhost:8080/redirectLoop' => ['code' => '---'],
            'http://localhost:8080/redirectToFound' => ['code' => 302 ],
            'http://localhost:8080/redirectToNotFound' => ['code' => 302 ],
            'http://localhost:8080/redirectToRedirectToNotFound' => ['code' => 302],
            'http://localhost:8080/timeout' => ['code' => '---'],
            'http://localhost:8080/twoRedirectsToSameLocation' => ['code' => 200],
        ], print_r($sitemap,true));
    }

    public function testFound(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/found');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/found' => ['code' => 200],
        ], print_r($sitemap,true));
    }

    public function testNotFound(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/notFound');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/notFound' => ['code' => 404],
        ], print_r($sitemap,true));
    }

    public function testExternalLink(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/externalLink');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/externalLink' => ['code' => 200],
            'http://example.com/' => ['code' => 200],
        ], print_r($sitemap,true));
    }

    public function testDeeplink(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/deeplink1');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/deeplink1' => ['code' => 200],
            'http://localhost:8080/deeplink2' => ['code' => 200],
            'http://localhost:8080/deeplink3' => ['code' => 200],
        ], print_r($sitemap,true));
    }

    public function testInterlinked(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/interlinked1');
        //$crawler->crawl('http://localhost:8080/page4'); // TODO!!! this ensures the order or results for the URL tracking test 3PARTS.
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/interlinked1' => ['code' => 200 , 'foundOn' => [
                'http://localhost:8080/interlinked1' => 1,
                'http://localhost:8080/interlinked2' => 1,
                'http://localhost:8080/interlinked3' => 1,
            ]],
            'http://localhost:8080/interlinked2' => ['code' => 200 , 'foundOn' => [
                'http://localhost:8080/interlinked1' => 1,
                'http://localhost:8080/interlinked2' => 1,
                'http://localhost:8080/interlinked3' => 1,
            ]],
            'http://localhost:8080/interlinked3' => ['code' => 200 , 'foundOn' => [
                'http://localhost:8080/interlinked1' => 1,
                'http://localhost:8080/interlinked2' => 1,
                'http://localhost:8080/interlinked3' => 1,
            ]],
        ], print_r($sitemap,true));
    }

    public function testRedirectToFound(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/redirectToFound');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/redirectToFound' => ['code' => 302],
            'http://localhost:8080/found' => ['code' => 200 ],
        ], print_r($sitemap,true));
    }

    public function testRedirectToNotFound(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/redirectToNotFound');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/redirectToNotFound' => ['code' => 302],
            'http://localhost:8080/notFound' => ['code' => 404 ],
        ], print_r($sitemap,true));
    }

    public function testRedirectToRedirectToNotFound(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/redirectToRedirectToNotFound');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/redirectToRedirectToNotFound' => ['code' => 302],
            'http://localhost:8080/redirectToNotFound' => ['code' => 302],
            'http://localhost:8080/notFound' => ['code' => 404],
        ], print_r($sitemap,true));
    }

    public function testTwoRedirectsToSameLocation(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/twoRedirectsToSameLocation');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/twoRedirectsToSameLocation' => ['code' => 200],
            'http://localhost:8080/redirect1' => ['code' => 302],
            'http://localhost:8080/redirect2' => ['code' => 302],
            'http://localhost:8080/found' => ['code' => 200],
        ], print_r($sitemap,true));
    }

    public function testTimeout(){
        $crawler=new Crawler([RequestOptions::CONNECT_TIMEOUT => 3, RequestOptions::TIMEOUT => 3]);
        $crawler->crawl('http://localhost:8080/timeout');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/timeout' => ['code' => '---'],
        ], print_r($sitemap,true));
    }

    public function testRedirectLoop(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/redirectLoop');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/redirectLoop' => ['code' => '---'],
        ], print_r($sitemap,true));
    }

    public function testInternalServerError(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/internalServerError');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/internalServerError' => ['code' => 500],
        ], print_r($sitemap,true));
    }

    public function testInvalidStatusCode(){
        $crawler=new Crawler();
        $crawler->crawl('http://localhost:8080/invalidStatusCode');
        $sitemap=$crawler->getResults();
        $this->assertTreeContains($sitemap,[
            'http://localhost:8080/invalidStatusCode' => ['code' => '---'],
        ], print_r($sitemap,true));
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
