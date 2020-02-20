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
        echo "Will:$url\n";
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
        // Retrieve both Redirect History headers
        $fullRedirectReport = [];
        if($response->getHeader('X-Guzzle-Redirect-History')){
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
        }

        foreach($fullRedirectReport as $rr){
            $this->results[]=[
                'location'=>(String)$rr['location'],
                'code'=>$rr['code'],
                'type'=>$response->getHeader('Content-Type')[0]??null,
                'foundOn'=>(string)$foundOnUrl,
            ];
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
        if($response=$requestException->getResponse()){
            $code=$response->getStatusCode();
            $type=$response->getHeader('Content-Type')[0]??null;
        }else{
            $code='???';
            $type='';
        }

        // Retrieve both Redirect History headers
        $fullRedirectReport = [];
        if($response && $response->getHeader('X-Guzzle-Redirect-History')){
            $redirectUriHistory = $response->getHeader('X-Guzzle-Redirect-History'); // retrieve Redirect URI history
            $redirectCodeHistory = $response->getHeader('X-Guzzle-Redirect-Status-History'); // retrieve Redirect HTTP Status history
            $fullRedirectReport=[$redirectUriHistory,$redirectCodeHistory];
        }

        $this->results[]=[
            'link'=>(String)$url,
            'code'=>$code,
            'type'=>$type,
            'parent'=>(string)$foundOnUrl,
            'redirects'=>$fullRedirectReport,
        ];
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling() {
        //print_r($this->results);
    }

}
