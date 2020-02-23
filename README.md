This is very similar to spatie/http-status-check but due to the way guzzle handles redirects I wasn't happy with the results; known 404's, and even whole areas of the site were missing from crawl results. By following this https://github.com/guzzle/guzzle/blob/master/docs/faq.rst#how-can-i-track-redirected-requests I was able to get a crawl result with the complete list of responses as I was expecting.

## Collate all FoundOnUrl's

Additionally I wanted to have a list of **all** the pages a 404 or specific link was found on. This was not possible as only the first FoundOn URL is reported. I created a patch to add a new function to the observer to make this possible. https://patch-diff.githubusercontent.com/raw/spatie/crawler/pull/280 without this the `CrawlerTest::testInterlinked` test will fail.

## Patch guzzle for invalid status code bug

There is a patch applied to guzzle to prevent the crawl failing with `EXCEPTION: Status code must be an integer value between 1xx and 5xx` https://github.com/spatie/crawler/issues/271 without this patch `CrawlerTest::testInvalidStatusCode` will fail.

## Tests

The node.js test server is copied directly from spatie/http-status-check but refactored to offer a more diverse range of tests cases that cover the redirect issues and new functionality described above.

The examples below are run against the test server in this project.

## More info about the redirects

By default `spatie/http-status-check` has `guzzle` set to not follow redirects. This results in the potential for parts of the site to be uncrawlable if they are behind a 301 or 302 redirect, and not linked internally anywhere else with a non-redirecting link.

Some webservers include a `<a href="destination">` link on the 301/302 body and this will mitigate the problem (spaite follows and indexes), however if the webserver does not do this, then the link won't be followed to its true destination, and the destination won't be indexed.

This is most obvious with a redirect to a not found page: You'd expect to see a 404 here:

```plain
./http-status-check scan http://localhost:8080/redirectToNotFound

Start scanning http://localhost:8080/redirectToNotFound

[2020-02-22 12:07:22] 302 Found - http://localhost:8080/redirectToNotFound

Crawling summary
----------------
Crawled 1 url(s) with statuscode 302
```

Or a redirect to a page thats found: You'd expect to see a 200 and the links of that page crawled too:
```plain
$ ./http-status-check scan http://localhost:8080/redirectToFound

Start scanning http://localhost:8080/redirectToFound

[2020-02-22 12:08:34] 302 Found - http://localhost:8080/redirectToFound

Crawling summary
----------------
Crawled 1 url(s) with statuscode 302
```

If I enable `RequestOptions::ALLOW_REDIRECTS` as suggested here https://github.com/spatie/crawler/issues/263 I get:

```plain
$ ./http-status-check scan http://localhost:8080/redirectToNotFound

Start scanning http://localhost:8080/redirectToNotFound

[2020-02-22 12:13:53] 404: Not Found - http://localhost:8080/redirectToNotFound

Crawling summary
----------------
Crawled 1 url(s) with statuscode 404
```

and

```plain
$ ./http-status-check scan http://localhost:8080/redirectToFound

Start scanning http://localhost:8080/redirectToFound

[2020-02-23 18:10:19] 200 OK - http://localhost:8080/redirectToFound

Crawling summary
----------------
Crawled 1 url(s) with statuscode 200
```

This looks better, we can see the 404 and the 200 now, however on closer inspection you'll notice there are no 302's being shown. And the `/redirectToFound` is actually showing as a 200 where the real response was 302, the 200 should be associated with the `/found` URL that is still missing from the results. For generating a sitemap of valid paged we'd need the destination URL, not the redirect URL.

Enter this sitemap crawler:

```plain
$ bin/crawler crawl http://localhost:8080/redirectToNotFound

302 http://localhost:8080/redirectToNotFound
404 http://localhost:8080/notFound

$ bin/crawler crawl http://localhost:8080/redirectToFound

302 http://localhost:8080/redirectToFound
200 http://localhost:8080/found
```
 