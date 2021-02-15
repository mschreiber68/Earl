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

        self::assertSame($s, (string) $url);
    }

    public function testScheme()
    {
        $url = Url::from('https:');
        self::assertSame('https', $url->scheme());

        $url->scheme('ftp://');
        self::assertSame('ftp', $url->scheme());
    }

    public function testPath()
    {
        $url = Url::from('https://website.com');
        self::assertSame('', $url->path());

        $url->path('my/path');
        self::assertSame('/my/path', $url->path());

        $url->path(function ($path) { return $path . '/next'; });
        self::assertSame('/my/path/next', $url->path());

        self::assertSame('https://website.com/my/path/next', (string) $url);
    }

    public function testSetQueryValue()
    {
        $url = Url::from('https://website.com')->q('a', 1);

        self::assertSame(1, $url->q('a'));
        self::assertSame('https://website.com?a=1', (string) $url);
    }

    public function testSetQueryWithCallable()
    {
        $url = Url::from('http://website.com?a=1')->q(function ($q) {
            $q['a'] += 1;
            return $q;
        });

        self::assertSame('http://website.com?a=2', (string) $url);
    }

    public function testQueryUrlEncoding()
    {
        $url = Url::from('http://website.com')
            ->q('a', '(myparam)! is encoded');

        self::assertSame('http://website.com?a=%28myparam%29%21+is+encoded', (string) $url);
    }

    public function testDefaults()
    {
        Url::defaults(['host' => 'website.com']);
        $url = Url::from('/my/path?a=1');

        self::assertSame('https://website.com/my/path?a=1', (string) $url);

        Url::defaults();
        $url = Url::from('/my/path?a=1');

        self::assertSame('/my/path?a=1', (string) $url);
    }
}
