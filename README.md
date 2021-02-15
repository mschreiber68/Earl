# Earl

Earl is a single class library for parsing, building, modifying, and printing URL strings.

Inspired by libraries like jQuery and [Requests](https://requests.readthedocs.io/en/master/) for Python, it aims to make working with URLs as convenient as possible.

# Usage 
```
use Earl\Url;
```

#### Creating:
```
// From a string
$url1 = Url::from('https://website.com/my/path?key=value');

// From another Earl\Url 
$url2 = Url::from($url1); 

// From an array
$url3 = Url::from([
    'host' => 'website.com',
    'path' => '/my/path',
    'query' => ['key' => 'value']  // can also use 'q'
]);

// cloneable
$url4 = clone $url3;

// Create empty url
$url5 = new Url();
```

#### Printing:
```
$urlString = (string) $url;
$urlString = $url->str();
```

#### Modifying:
```
$url = new Url();

// Argument sets value
$url->host('website.com');

// No arguments gets value 
$host = $url->host();

// Fluent setters
$url->path('my/path')
    ->path($url->path() . '/morepath');

assert($url == 'https://website.com/my/path/morepath');
```

### Working with query data
```
// Get param value
$url->q('a');

// Get entire query
$url->q();

// Set query param
$url->q('a', 1);

// Set entire query
$url->q(['a' => 1, 'b' => 2])

// Null values will not appear in URL string
$url->q('a', null);
```

Query encoding and decoding has the same behavior as [parse_url](https://www.php.net/manual/en/function.parse-url) and [http_build_query](https://www.php.net/manual/en/function.http-build-query.php)
```
$url = Url::from('http://website.com?a=%28myparam%29%21+is+encoded');
assert($url->q('a') == '(myparam)! is encoded');

$url = Url::from('http://website.com')->q('a', '(myparam)! is encoded');
assert($url == 'http://website.com?a=%28myparam%29%21+is+encoded');
```

### Auth
```
$url = Url::from('http://website.com')
    ->user('me@email.com')
    ->pass('secret');

assert($url == 'http://me%40email.com:secret@website.com');
```

### Global defaults
You may be working in a context where most of the URLs you build will have the same scheme, host, etc.
You can set global defaults for these values:
```
Url::defaults(['scheme' => 'https', 'host' => 'www.mywebsite.com']);

$url = Url::from('/my/path?a=1');
assert($url == 'https://www.mywebsite.com/my/path?a=1');

// Clear defaults
Url::defaults();
```

The default scheme is `https`

Currently only `scheme` and `host` are supported as defaults.
