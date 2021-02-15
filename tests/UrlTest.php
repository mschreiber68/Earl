<?php

use Earl\Url;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
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
        $url = new Url('https:');
        self::assertSame('https', $url->scheme());

        $url->scheme('ftp://');
        self::assertSame('ftp', $url->scheme());
    }
}
