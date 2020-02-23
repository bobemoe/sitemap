"use strict";

let app = require('express')();

app.get('/', function (request, response) {
    response.end([
        '<ul>',
        '    <li><a href="/found">found</a></li>',
        '    <li><a href="/notFound">notFound</a></li>',
        '    <li><a href="/externalLink">external_link</a></li>',
        '    <li><a href="/deeplink1">deeplink1</a></li>',
        '    <li><a href="/interlinked1">interlinked1</a></li>',
        '    <li><a href="/redirectToFound">redirectToFound</a></li>',
        '    <li><a href="/redirectToNotFound">redirectToNotFound</a></li>',
        '    <li><a href="/redirectLoop">redirectLoop</a></li>',
        '    <li><a href="/timeout">timeout</a></li>',
        '    <li><a href="/internalServerError">internalServerError</a></li>',
        '    <li><a href="/twoRedirectsToSameLocation">twoRedirectsToSameLocation</a></li>',
        '    <li><a href="mailto:test@example.com">mailto</a></li>',
        '    <li><a href="tel:+4412345678">tel</a></li>',
        '</ul>',
    ].join('\n'));
});

app.get('/externalLink', function (request, response) {
    response.end('<a href="http://example.com/"</a>');
});

app.get('/deeplink1', function (request, response) {
    response.end('<a href="/deeplink2">l</a>');
});
app.get('/deeplink2', function (request, response) {
    response.end('<a href="/deeplink3">l</a>');
});
app.get('/deeplink3', function (request, response) {
    response.end('<a href="/deeplink4">l</a>');
});
app.get('/deeplink4', function (request, response) {
    response.end('<a href="/deeplink5">l</a>');
});

app.get('/found', function (request, response) {
    response.end('this page is found');
});

app.get('/redirectToNotFound', function (request, response) {
    response.redirect('/notFound');
});
app.get('/redirectToRedirectToNotFound', function (request, response) {
    response.redirect('/redirectToNotFound');
});

app.get('/redirectToFound', function (request, response) {
    response.redirect('/found');
});

app.get('/redirectLoop', function (request, response) {
    response.redirect('/redirectLoop');
});

app.get('/twoRedirectsToSameLocation', function (request, response) {
    response.end('<a href="/redirect1">r1</a><a href="/redirect2">r2</a>');
});
app.get('/redirect1', function (request, response) {
    response.redirect('/found');
});
app.get('/redirect2', function (request, response) {
    response.redirect('/found');
});

app.get('/timeout', function (request, response) {
    // no response
});

app.get('/internalServerError', function (request, response) {
    response.status(500).end();
});

app.get('/interlinked1', function (request, response) {
    response.end('<a href="/interlinked1">1</a><a href="/interlinked2">2</a><a href="/interlinked3">3</a>');
});
app.get('/interlinked2', function (request, response) {
    response.end('<a href="/interlinked1">1</a><a href="/interlinked2">2</a><a href="/interlinked3">3</a>');
});
app.get('/interlinked3', function (request, response) {
    response.end('<a href="/interlinked1">1</a><a href="/interlinked2">2</a><a href="/interlinked3">3</a>');
});

let server = app.listen(8080, function () {
    const host = 'localhost';
    const port = server.address().port;

    console.log('Testing server listening at http://%s:%s', host, port);
});
