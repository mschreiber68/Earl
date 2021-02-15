<?php

namespace Earl;

class Url
{
    private static $defaultScheme = 'https';
    private static $defaultHost = '';

    private $scheme;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $path;
    private $query;
    private $fragment;

    public function __construct()
    {
        $this->scheme = self::$defaultScheme;
        $this->host = self::$defaultHost;
        $this->port = null;
        $this->user = '';
        $this->pass = '';
        $this->path = '';
        $this->query = [];
        $this->fragment = '';
    }

    public static function defaults(array $arg = [])
    {
        self::$defaultScheme = $arg['scheme'] ?? 'https';
        self::$defaultHost = $arg['host'] ?? '';
    }

    /**
     * @param string|Url|array $obj
     * @throws \InvalidArgumentException
     */
    public static function from($obj): self
    {
        $url = new self();

        if ($obj instanceof self) {
            return clone $obj;
        }

        if (is_string($obj)) {
            $parsed = parse_url($obj);
            if ($parsed === false) {
                throw new \InvalidArgumentException("Failed to parse URL string: {$obj}");
            }

            $url->scheme = $parsed['scheme'] ?: self::$defaultScheme;
            $url->host = $parsed['host'] ?? self::$defaultHost;
            $url->port = $parsed['port'] ?? null;
            $url->user = $parsed['user'] ?? '';
            $url->pass = $parsed['pass'] ?? '';
            $url->path = $parsed['path'] ?? '';
            $url->query = isset($parsed['query']) ? $url->parseQs($parsed['query']) : [];
            $url->fragment = $parsed['fragment'] ?? '';

            return $url;
        }

        if (is_array($obj)) {
            $url->scheme($obj['scheme'] ?? self::$defaultScheme);
            $url->host($obj['host'] ?? self::$defaultScheme);
            $url->port($obj['port'] ?? null);
            $url->user($obj['user'] ?? '');
            $url->pass($obj['pass'] ?? '');
            $url->path($obj['path'] ?? '');
            $url->q($obj['query'] ?? $obj['q'] ?? []);
            $url->fragment($obj['fragment'] ?? '');

            return $url;
        }

        throw new \InvalidArgumentException("Invalid type: " . gettype($obj));
    }

    public function __toString()
    {
        $fullPath = $this->path;
        if ($this->query) {
            $fullPath .= '?' . $this->buildQs();
        }
        if ($this->fragment) {
            $fullPath .= '#' . $this->fragment;
        }

        if (!$this->host) {
            return $fullPath;
        }

        $url = $this->host;
        if ($this->scheme) {
            $url = "{$this->scheme}://{$url}";
        }
        if ($this->port) {
            $url .= ":{$this->port}";
        }

        return $url . $fullPath;
    }

    /**
     * Alias for __toString()
     */
    public function str(): string
    {
        return $this->__toString();
    }

    public function scheme(string $arg = '')
    {
        if (func_num_args() === 0) {
            return $this->scheme;
        }

        $this->scheme = str_replace([':', '/'], [], $arg);
        return $this;
    }

    public function user(string $arg = '')
    {
        if (func_num_args() === 0) {
            return $this->user;
        }

        $this->user = $arg;
        return $this;
    }

    public function pass(string $arg = '')
    {
        if (func_num_args() === 0) {
            return $this->pass;
        }

        $this->pass = $arg;
        return $this;
    }

    public function host(string $arg = '')
    {
        if (func_num_args() === 0) {
            return $this->host;
        }

        $this->host = $arg;
        return $this;
    }

    public function port(?int $arg = null)
    {
        if (func_num_args() === 0) {
            return $this->port;
        }

        $this->port = $arg;
        return $this;
    }

    public function path(string $arg = '')
    {
        if (func_num_args() === 0) {
            return $this->path;
        }

        $this->path = $arg;

        if ($this->path && strpos($this->path, '/') !== 0) {
            $this->path = '/' . $this->path;
        }
        return $this;
    }

    public function q($qKey = null, $qVal = null)
    {
        if (func_num_args() === 2) {
            if ($qVal === null) {
                unset($this->query[$qKey]);
            } else {
                $this->query[$qKey] = $qVal;
            }

            return $this;
        }

        if (func_num_args() === 1) {
            if (is_array($qKey)) {
                $this->query = $qKey;
                return $this;
            }

            return $this->query[$qKey] ?? null;
        }

        return $this->query;
    }

    public function fragment(string $arg = '')
    {
        if (func_num_args() === 0) {
            return $this->fragment;
        }

        $this->fragment = $arg;
        return $this;
    }

    private function parseQs(string $qs): ?array
    {
        parse_str($qs, $arr);
        return $arr;
    }

    private function buildQs(): string
    {
        $query = array_filter($this->query, static function ($val) { return $val !== null; });
        return $query ? http_build_query($query) : '';
    }
}