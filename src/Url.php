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
        $this->port = '';
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
     * @throws \InvalidArgumentException
     */
    public static function from($obj): self
    {
        $url = new self();

        if ($obj instanceof self) {
            $url->copy($obj);
        }

        else if (is_string($obj)) {
            $parsed = parse_url($obj);
            if ($parsed === false) {
                throw new \InvalidArgumentException("Failed to parse URL string: {$obj}");
            }

            $url->scheme = $parsed['scheme'] ?: self::$defaultScheme;
            $url->host = $parsed['host'] ?? self::$defaultHost;
            $url->port = $parsed['port'] ?? '';
            $url->user = $parsed['user'] ?? '';
            $url->pass = $parsed['pass'] ?? '';
            $url->path = $parsed['path'] ?? '';
            $url->query = isset($parsed['query']) ? $url->parseQs($parsed['query']) : [];
            $url->fragment = $parsed['fragment'] ?? '';
        }
        else {
            throw new \InvalidArgumentException("Invalid type: " . gettype($obj));
        }

        return $url;
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

    public function scheme($arg = null)
    {
        if (func_num_args() === 0) {
            return $this->scheme;
        }

        $this->scheme = str_replace([':', '/'], [], $arg);
        return $this;
    }

    public function host($arg = null)
    {
        if (func_num_args() === 0) {
            return $this->host;
        }

        $this->host = $arg;
        return $this;
    }

    public function port($arg = null)
    {
        if (func_num_args() === 0) {
            return $this->port;
        }

        $this->port = $arg;
        return $this;
    }

    public function path($arg = null)
    {
        if (func_num_args() === 0)
        {
            return $this->path;
        }

        if (is_callable($arg))
        {
            $this->path = call_user_func($arg, $this->path);
        } else {
            $this->path = $arg;
        }

        if (strpos($this->path, '/') !== 0)
        {
            $this->path = '/' . $this->path;
        }
        return $this;
    }

    public function q($qKey = null, $qVal = null)
    {
        if (func_num_args() === 2)
        {
            $this->query[$qKey] = $qVal;
            return $this;
        }

        if (func_num_args() === 1)
        {
            if (is_callable($qKey)) {
                $this->query = call_user_func($qKey, $this->query);
                return $this;
            }

            return $this->query[$qKey] ?? null;
        }

        return $this->query;
    }

    public function fragment($arg = null)
    {
        if (func_num_args() === 0) {
            return $this->fragment;
        }

        $this->fragment = $arg;
        return $this;
    }

    private function copy(Url $other): void
    {
        $this->scheme = $other->scheme;
        $this->host = $other->host;
        $this->port = $other->port;
        $this->path = $other->path;
        $this->query = $other->query;
        $this->fragment = $other->fragment;
    }

    private function parseQs($qs): ?array
    {
        parse_str($qs, $arr);
        return $arr;
    }

    private function buildQs(): string
    {
        return $this->query ? http_build_query($this->query) : '';
    }
}