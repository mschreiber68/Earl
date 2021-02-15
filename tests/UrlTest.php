<?php

use Earl\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    public function setUp(): void
    {
        Url::defaults();
    }

    public function testFromString()
    {
        $s = 'http://www.website.com/my/path/?a=1&b=2#banana';
        $url = Url::from($s);

        self::assertSame('http', $url->scheme());
        self::assertSame('www.website.com', $url->host());
        self::assertSame('/my/path/', $url->path());
        self::assertSame(['a' => '1', 'b' => '2'], $url->q());
        self::assertSame('banana', $url->fragment());

        self::assertEquals($s, $url);
    }

    public function testFromArray()
    {
        $url = Url::from([
            'host' => 'website.com',
            'port' => 443,
            'query' => ['a' => 1]
        ]);

        self::assertEquals('https://website.com:443?a=1', $url);
    }

    public function testScheme()
    {
        $url = Url::from('https:');
        self::assertSame('https', $url->scheme());

        $url->scheme('ftp://');
        self::assertSame('ftp', $url->scheme());
    }

    public function testUserPass()
    {
        $url = Url::from('http://website.com')
            ->user('me@email.com')
            ->pass('secret');

        self::assertEquals('http://me%40email.com:secret@website.com', $url);
    }

    public function testPath()
    {
        $url = Url::from('https://website.com');
        self::assertSame('', $url->path());

        $url->path('my/path');
        self::assertSame('/my/path', $url->path());

        $url->path($url->path() . '/next');
        self::assertSame('/my/path/next', $url->path());

        self::assertEquals('https://website.com/my/path/next', $url);
    }

    public function testSetQueryValue()
    {
        $url = Url::from('https://website.com')->q('a', 1);

        self::assertSame(1, $url->q('a'));
        self::assertEquals('https://website.com?a=1', $url);
    }

    public function testSetQueryParamNullRemovesParam()
    {
        $url = Url::from('http://website.com?a=1')->q('a', null);

        self::assertEquals('http://website.com', $url);
    }

    public function testQueryUrlEncoding()
    {
        $url = Url::from('http://website.com')
            ->q('a', '(myparam)! is encoded');

        self::assertEquals('http://website.com?a=%28myparam%29%21+is+encoded', $url);
    }

    public function testQueryUrlDecoding()
    {
        $url = Url::from('http://website.com?a=%28myparam%29%21+is+encoded');

        self::assertSame('(myparam)! is encoded', $url->q('a'));
    }

    public function testQueryWithArrays()
    {
        $q = [
            'a' => '1',
            'b' => ['2', '3', '4']
        ];

        $url = Url::from('http://website.com')->q($q);

        self::assertEquals('http://website.com?a=1&b%5B0%5D=2&b%5B1%5D=3&b%5B2%5D=4', $url);

        $url = Url::from('http://website.com?a=1&b%5B0%5D=2&b%5B1%5D=3&b%5B2%5D=4');
        self::assertSame($q, $url->q());
    }

    public function testNoHostPrintsPartialUrl()
    {
        $url = Url::from('/my/path?a=1#frag')
            ->q('b', 2);

        self::assertEquals('/my/path?a=1&b=2#frag', $url);
    }

    public function testDefaults()
    {
        Url::defaults(['host' => 'website.com']);
        $url = Url::from('/my/path?a=1');

        self::assertEquals('https://website.com/my/path?a=1', $url);

        Url::defaults();
        $url = Url::from('/my/path?a=1');

        self::assertEquals('/my/path?a=1', $url);
    }
}
