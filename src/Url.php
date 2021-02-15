<?php

namespace Earl;

class Url
{
    private $scheme = 'https';
    private $host = '';
    private $port = '';
    private $user = '';
    private $pass = '';
    private $path = '';
    private $query = [];
    private $fragment = '';

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
                throw new \InvalidArgumentException("Failed to parse URL string: {$entity}");
            }

            $url->scheme = $parsed['scheme'] ?? 'https';
            $url->host = $parsed['host'] ?? '';
            $url->port = $parsed['port'] ?? '';
            $url->user = $parsed['user'] ?? '';
            $url->pass = $parsed['pass'] ?? '';
            $url->path = $parsed['path'] ?? '';
            $url->query = isset($parsed['query']) ? $url->parseQs($parsed['query']) : [];
            $url->fragment = $parsed['fragment'] ?? '';
        }
        else {
            throw new \InvalidArgumentException("Invalid type: " . gettype($entity));
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

        if (substr($fullPath, 0, 1) !== '/') {
            $url .= '/';
        }

        return $url . $fullPath;

    }

    public function offsetExists($offset)
    {
        return isset($this->query[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->q($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->q($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->query[$offset]);
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
        if (func_num_args() === 0) {
            return $this->path;
        }

        $this->path = $arg;
        return $this;
    }

    public function q($qKey = null, $qVal = null)
    {
        if (func_num_args() === 2) {
            $this->query[$qKey] = $qVal;
            return $this;
        } elseif (func_num_args() === 1) {
            return $this->query[$qKey] ?? null;
        } else {
            return $this->query;
        }
    }

    public function fragment($arg = null)
    {
        if (func_num_args() === 0) {
            return $this->fragment;
        }

        $this->fragment = $arg;
        return $this;
    }

    /**
     * @param Url $other
     */
    private function copy($other)
    {
        $this->scheme = $other->scheme;
        $this->host = $other->host;
        $this->port = $other->port;
        $this->path = $other->path;
        $this->query = $other->query;
        $this->fragment = $other->fragment;
    }

    private function parseQs($qs)
    {
        parse_str($qs, $arr);
        return $arr;
    }

    private function buildQs()
    {
        return $this->query ? http_build_query($this->query) : '';
    }

}