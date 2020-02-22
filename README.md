This link checker / sitemap generator is built using spatie/crawler which uses guzzle to do the crawling.

This is very similar to spatie/http-status-check but intends to add some functionality.

The nodejs test server is copied directly from spatie/http-status-check with some additional pages added. The examples below will br run against the test server in this repo.

## Follow redirects

By default `spatie/http-status-check` has `guzzle` set to not follow redirects. This results in the potential for parts of the site to be uncrawlable if they are behind a 301 or 302 redirect, and not linked internally anywhere else with a non-redirecting link.

Some webservers include a `<a href="destination">` link on the 301/302 body and this will mitigate the problem (spaite indexes and follows it), however if the webserver does not do this, then the link wont be followed to its true destination, and the destination won't be crawled.

This is most obvious with a redirect to a not found page: You'd expect to see a 404 here:

```
./http-status-check scan http://localhost:8080/redirectToNotFound

Start scanning http://localhost:8080/redirectToNotFound

[2020-02-22 12:07:22] 302 Found - http://localhost:8080/redirectToNotFound

Crawling summary
----------------
Crawled 1 url(s) with statuscode 302
```

Or a redirect to a page with links: You'd expect to see the rest of the site here:
```
$ ./http-status-check scan http://localhost:8080/redirectToFound

Start scanning http://localhost:8080/redirectToFound

[2020-02-22 12:08:34] 302 Found - http://localhost:8080/redirectToFound

Crawling summary
----------------
Crawled 1 url(s) with statuscode 302
```

If I enable RequestOptions::ALLOW_REDIRECTS I get:

```
bob@chodbox:~/Projects/http-status-check$ ./http-status-check scan http://localhost:8080/redirectToNotFound

Start scanning http://localhost:8080/redirectToNotFound

[2020-02-22 12:13:53] 404: Not Found - http://localhost:8080/redirectToNotFound

Crawling summary
----------------
Crawled 1 url(s) with statuscode 404
```

and

```
$ ./http-status-check scan http://localhost:8080/redirectToFound

Start scanning http://localhost:8080/redirectToFound

[2020-02-22 12:13:45] 200 OK - http://localhost:8080/redirectToFound
[2020-02-22 12:13:45] 200 OK - http://localhost:8080/link1
[2020-02-22 12:13:45] 200 OK - http://localhost:8080/link2
[2020-02-22 12:13:45] 200 OK - http://localhost:8080/link4
[2020-02-22 12:13:45] 200 OK - http://localhost:8080/link3
[2020-02-22 12:13:45] 200 OK - http://example.com/
[2020-02-22 12:13:45] 404: Not Found - http://localhost:8080/notExists (found on http://localhost:8080/link3)

Crawling summary
----------------
Crawled 6 url(s) with statuscode 200
Crawled 1 url(s) with statuscode 404
```

This looks better, we can see the 404 now, and the rest of the site being index, however on closer inspection you'll notice there are no 301 or 302's being shown. And the redirectToFound is actually showing as a 200. The homepage is missing.

Enter my sitemap crawler:

```
$ bin/crawler crawl http://localhost:8080/redirectToNotFound

302 http://localhost:8080/redirectToNotFound
404 http://localhost:8080/notFound2
```

and

```
302 http://localhost:8080/redirectToFound
200 http://localhost:8080/
200 http://localhost:8080/link1
200 http://localhost:8080/link2
302 http://localhost:8080/link4
200 http://example.com/
200 http://localhost:8080/link3
404 http://localhost:8080/notExists
```

This seems to be a feature of guzzle and not really a bug of spatie. https://github.com/guzzle/guzzle/blob/master/docs/faq.rst#how-can-i-track-redirected-requests

## Collate `foundOnUrl`s.
    * Requires a patch to spatie/crawler https://patch-diff.githubusercontent.com/raw/spatie/crawler/pull/280 (auto applied)
    * TODO: Document the confusing PART1&2 behaviour. (collating the redirect urls)
