<?php
namespace JHodges\Sitemap;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlInternalUrls;

class CrawlObserver extends \Spatie\Crawler\CrawlObserver
{

    public $results=[];

    /**
     * Called when the crawler will crawl the url.
     *
     * @param \Psr\Http\Message\UriInterface $url
     */
    public function willCrawl(UriInterface $url)
    {
    }

    /**
     * Called when the crawler has crawled the given url successfully.
     *
     * @param \Psr\Http\Message\UriInterface $url
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null
    ){
        // https://github.com/guzzle/guzzle/blob/master/docs/faq.rst#how-can-i-track-redirected-requests
        if($response->getHeader('X-Guzzle-Redirect-History')){
            // Retrieve both Redirect History headers
            $fullRedirectReport = [];
            // Retrieve both Redirect History headers
            $redirectUriHistory = $response->getHeader('X-Guzzle-Redirect-History'); // retrieve Redirect URI history
            $redirectCodeHistory = $response->getHeader('X-Guzzle-Redirect-Status-History'); // retrieve Redirect HTTP Status history
            // Add the initial URI requested to the (beginning of) URI history
            array_unshift($redirectUriHistory, (string)$url);
            // Add the final HTTP status code to the end of HTTP response history
            array_push($redirectCodeHistory, $response->getStatusCode());
            $fullRedirectReport = [];
            foreach ($redirectUriHistory as $key => $value) {
                $fullRedirectReport[$key] = ['location' => $value, 'code' => $redirectCodeHistory[$key]];
            }

            // PART 2a/2: has this url been found already
            $fos=$this->results[(string)$url]['foundOn']??[];

            foreach($fullRedirectReport as $k=>$redirect){
                $this->addResult(
                    (String)$redirect['location'],
                    (string)$foundOnUrl,
                    $redirect['code'],
                    $response->getHeader('Content-Type')[0]??null,
                    $k == 0 ? $fullRedirectReport : []
                );
                // PART 2b/2: this redirecting url has only just been crawled.
                // but if it has already been found on other pages
                // this new redirect history needs to have those foundOnUrls added too
                if($k>0){
                    foreach($fos as $kk=>$vv){
                        $this->addResult(
                            (String)$redirect['location'],
                            (string)$kk,
                            $redirect['code'],
                            $response->getHeader('Content-Type')[0]??null
                        );
                    }
                }
            }
        }else{
            $this->addResult(
                (String)$url,
                (string)$foundOnUrl,
                $response->getStatusCode(),
                $response->getHeader('Content-Type')[0]??null
            );
        }
    }

    /**
     * Called when the crawler has found the url again
     * NOTE: That this may be called before crawled or crawlFailed is called for this URL
     *
     * @param \Psr\Http\Message\UriInterface $url
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    public function alreadyCrawled(
        UriInterface $url,
        ?UriInterface $foundOnUrl = null
    ){
        // PART 1/2: if there is an existing result with known redirects
        // then its redirects must apply to this page
        if(count($this->results[(string)$url]['redirects']??[])>0){
            foreach($this->results[(string)$url]['redirects'] as $redirect){
                $this->addResult($redirect['location'],(string)$foundOnUrl);
            }
        }else{
            $this->addResult((String)$url,(string)$foundOnUrl);
        }
    }

    /**
     * Called when the crawler had a problem crawling the given url.
     *
     * @param \Psr\Http\Message\UriInterface $url
     * @param \GuzzleHttp\Exception\RequestException $requestException
     * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ){
        if( $response=$requestException->getResponse() ){
            $this->crawled($url,$response,$foundOnUrl);
        }else{
            $this->addResult((String)$url,(string)$foundOnUrl);
        }
    }

    public function addResult($url, $foundOn, $code='', $type='', $redirects=[]){
        if(!isset($this->results[$url])){
            $this->results[$url]=[];
        }
        if(!isset($this->results[$url]['code']) || !$this->results[$url]['code']){
            $this->results[$url]['code']=$code;
        }
        if(!isset($this->results[$url]['type']) || !$this->results[$url]['type']){
            $this->results[$url]['type']=$type;
        }
        if(isset($this->results[$url]['foundOn'][$foundOn])){
            $this->results[$url]['foundOn'][$foundOn]++;
        }else{
            $this->results[$url]['foundOn'][$foundOn]=1;
        }
        if(!isset($this->results[$url]['redirects']) || !$this->results[$url]['redirects']){
            $this->results[$url]['redirects']=$redirects;
        }
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling() {
    }

}
