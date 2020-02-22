"use strict";

let app = require('express')();

app.get('/', function (request, response) {
    response.end('<a href="/link1">Link1</a><a href="/link2">Link2</a><a href="/link4">Link4</a><a href="mailto:test@example.com">Email</a>');
});

app.get('/link1', function (request, response) {
    response.end('You are on link1<a href="http://example.com/"</a>');
});

app.get('/link2', function (request, response) {
    response.end('You are on link2<a href="/link1">Link1</a><a href="/link3">Link3</a>');
});

app.get('/link3', function (request, response) {
    response.end('You are on link3<a href="/link1">Link1</a><a href="/notExists">not exists</a>');
});

app.get('/link4', function (request, response) {
    response.redirect('/link1');
});

app.get('/redirectToNotFound', function (request, response) {
    response.redirect('/notFound2');
});

app.get('/redirectToFound', function (request, response) {
    response.redirect('/');
});

app.get('/redirect1', function (request, response) {
    response.redirect('/link1');
});

app.get('/redirect2', function (request, response) {
    response.redirect('/link1');
});

app.get('/twoRedirectsToSameLocation', function (request, response) {
    response.end('<a href="/redirect1">r1</a><a href="/redirect2">r2</a>');
});

app.get('/redirectToRedirectToNotFound', function (request, response) {
    response.redirect('/redirectToNotFound');
});

app.get('/timeout', function (request, response) {
    // no response
});

app.get('/internalServerError', function (request, response) {
    response.status(500).end();
});

app.get('/page1', function (request, response) {
    response.end('<a href="/page1">Page1</a><a href="/page2">Page2</a><a href="/page3">Page3</a><a href="/notFound1">NotFound</a><a href="/redirectToRedirectToNotFound">redirectToRedirectToNotFound</a>');
});
app.get('/page2', function (request, response) {
    response.end('<a href="/page1">Page1</a><a href="/page2">Page2</a><a href="/page3">Page3</a><a href="/notFound1">NotFound</a><a href="/redirectToRedirectToNotFound">redirectToRedirectToNotFound</a>');
});
app.get('/page3', function (request, response) {
    response.end('<a href="/page1">Page1</a><a href="/page2">Page2</a><a href="/page3">Page3</a><a href="/notFound1">NotFound</a><a href="/redirectToRedirectToNotFound">redirectToRedirectToNotFound</a>');
});
app.get('/page4', function (request, response) {
    response.end('<a href="/redirectToRedirectToNotFound">redirectToRedirectToNotFound</a>');
});

let server = app.listen(8080, function () {
    const host = 'localhost';
    const port = server.address().port;

    console.log('Testing server listening at http://%s:%s', host, port);
});
