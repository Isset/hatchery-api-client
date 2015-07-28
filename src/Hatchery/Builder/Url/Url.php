<?php

namespace Hatchery\Builder\Url;

use Hatchery\Builder\Exception\InvalidUrlException;

/**
 * Class Url
 *
 * @package IssetBV\Hatchery\Builder\Url
 * @author Tim Fennis <tim@isset.nl>
 */
class Url
{
    const SCHEME = PHP_URL_SCHEME;
    const HOST = PHP_URL_HOST;
    const PORT = PHP_URL_PORT;
    const USER = PHP_URL_USER;
    const PASS = PHP_URL_PASS;
    const PATH = PHP_URL_PATH;
    const QUERY = PHP_URL_QUERY;
    const FRAGMENT = PHP_URL_FRAGMENT;
    const LTRIM_PATH = 1;
    const RTRIM_PATH = 2;

    /**
     * @var string
     */
    protected $fragment;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $pass;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var integer
     */
    protected $port;

    /**
     * @var string
     */
    protected $query;

    /**
     * @var string
     */
    protected $scheme;

    /**
     * @var string
     */
    protected $user;

    /**
     * @param $url
     * @throws InvalidUrlException
     */
    public function __construct($url)
    {
        //@todo Remove parse url dependency
        $this->scheme = parse_url($url, Url::SCHEME);
        if ($this->scheme === false) {
            throw new InvalidUrlException('The scheme part of your URL could not be parsed');
        }

        $this->host = parse_url($url, Url::HOST);
        if ($this->host === false) {
            throw new InvalidUrlException('The host part of your URL could not be parsed');
        }

        $this->port = parse_url($url, Url::PORT);
        if ($this->port === false) {
            throw new InvalidUrlException('The port part of your URL could not be parsed');
        }

        $this->user = parse_url($url, Url::USER);
        if ($this->user === false) {
            throw new InvalidUrlException('The user part of your URL could not be parsed');
        }

        $this->pass = parse_url($url, Url::PASS);
        if ($this->pass === false) {
            throw new InvalidUrlException('The password part of your URL could not be parsed');
        }

        // From: https://tools.ietf.org/html/rfc1738#section-3.1
        //
        // url-path
        //    The rest of the locator consists of data specific to the
        //    scheme, and is known as the "url-path". It supplies the
        //    details of how the specified resource can be accessed. Note
        //    that the "/" between the host (or port) and the url-path is
        //    NOT part of the url-path.

        $pathWithRetardedSlash = parse_url($url, Url::PATH);

        if ($pathWithRetardedSlash === '/') {
            $this->path = '';
        } elseif ($pathWithRetardedSlash{0} === '/') {
            $this->path = substr($pathWithRetardedSlash, 1);
        } else {
            $this->path = $pathWithRetardedSlash; // Apparently not
        }

        if ($this->path === false) {
            throw new InvalidUrlException('The path part of your URL could not be parsed in ' . $url);
        }

        // Parse query part
        $query = parse_url($url, Url::QUERY);
        $this->query = [];
        if ($query !== null && $query !== false) {
            foreach (explode('&', $query) as $keyValuePair) {
                # split into name and value
                list($key, $value) = explode('=', $keyValuePair, 2);

                $this->addQueryParameter($key, $value);
            }
        }

        // Parse fragment part
        $fragment = parse_url($url, Url::FRAGMENT);
        if (true === is_string($fragment)) {
            $this->fragment = $fragment;
        } else {
            $this->fragment = null;
        }

        // Post URL checks
        if ($this->scheme === null) {
            throw new InvalidUrlException('Your URL must contain a valid scheme');
        }
    }

    /**
     * This method usually expects its variables to be decoded
     *
     * @param string $key
     * @param string $value
     * @param boolean $decode if you pass true as the 3rd argument the variables will be decoded
     */
    public function addQueryParameter($key, $value, $decode = false)
    {
        if ($decode === true) {
            $key = rawurldecode($key);
            $value = rawurldecode($value);
        }
        $this->query[crc32($key)] = [
            'key' => $key,
            'value' => $value
        ];
    }

    /**
     * @param string $path
     * @return string
     */
    public static function getFilenameFromPath($path)
    {
        return substr($path, strrpos($path, '/') + 1);
    }

    /**
     * @return Url
     */
    public function copy()
    {
        return new Url($this->parseUrl());
    }

    /**
     * @param array $parts
     * @param boolean $urlEncode
     * @return string
     */
    public function parseUrl(
        $parts = [
            Url::SCHEME,
            Url::HOST,
            Url::PORT,
            Url::USER,
            Url::PASS,
            Url::PATH,
            Url::QUERY,
            Url::FRAGMENT
        ],
        $urlEncode = false
    ) {
        if (count($parts) === 0) {
            return '';
        }

        $buf = '';

        if ($this->scheme !== null && in_array(Url::SCHEME, $parts)) {
            $buf .= $this->scheme . '://';
        }

        if ($this->user !== null && in_array(Url::USER, $parts)) {
            $buf .= $this->user;

            if ($this->pass !== null && in_array(Url::PASS, $parts)) {
                $buf .= ':' . $this->pass;
            }
        }

        if ($this->host !== null && in_array(Url::HOST, $parts)) {
            if ($this->user !== null && in_array(Url::USER, $parts)) {
                $buf .= '@';
            }

            $buf .= $this->host;
        }

        if ($this->port !== null && in_array(Url::PORT, $parts)) {
            $buf .= ':' . $this->port;
        }

        if ($this->path !== null && in_array(Url::PATH, $parts)) {
            $urlParts = explode('/', $this->path);

            if ($urlEncode === true) {
                $urlParts = array_map('rawurlencode', $urlParts);
            }

            $buf .= '/' . implode('/', $urlParts);
        }

        if (is_array($this->query) && in_array(Url::QUERY, $parts)) {

            //@todo support array values
            $keyValuePairStrings = [];
            foreach ($this->query as $keyValuePairArray) {
                $keyValuePairStrings[] = rawurlencode($keyValuePairArray['key']) . '=' . rawurlencode($keyValuePairArray['value']);
            }

            if (count($keyValuePairStrings) > 0) {
                $buf .= '?' . implode('&', $keyValuePairStrings);
            }
        }

        if ($this->fragment !== null && in_array(Url::FRAGMENT, $parts)) {
            $buf .= '#' . $this->fragment;
        }

        return $buf;
    }

    /**
     * @return string|null
     */
    public function getBasename()
    {
        return $this->getFilename(true);
    }

    /**
     * Returns the filename + extension. If the URL is a directory this function returns ''(empty string)
     *
     * @param bool $includeExtension If you set this to false the extension will not be returned as part of the filename
     * @return string
     */
    public function getFilename($includeExtension = true)
    {
        if ($this->path === null) {
            return '';
        }

        // If the given path is a directory return an empty string
        if ($this->isDir() === true) {
            return '';
        }

        if (strrpos($this->path, '/') !== false) {
            $filename = substr($this->path, strrpos($this->path, '/') + 1); // everything after last slash is filename
        } else {
            $filename = $this->path;
        }

        if (strrpos($filename, '.') !== false) {
            $filename = substr($filename, 0, strrpos($filename, '.')); // Strip the extension
            $extension = substr($this->path, strrpos($this->path, '.') + 1); // everything after last dot is extension
        } else {
            $extension = '';
        }

        if ($extension !== '' && $includeExtension) {
            $filename .= '.' . $extension;
        }

        return $filename;
    }

    /**
     * @return boolean
     */
    public function isDir()
    {
        if (stripos(strrev($this->path), '/') === 0) {
            return true;
        }

        return false;
    }

    /**
     * Note that this function may not be safe if any part of the url is invalid and validation is currently not complete in this object
     *
     * @return string
     */
    public function getFilenameExtension()
    {
        if (strrpos($this->path, '.') === false) {
            return '';
        }

        return substr($this->path, strrpos($this->path, '.') + 1);
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPass()
    {
        return rawurldecode($this->pass);
    }

    /**
     * @param array $options
     * @return string
     */
    public function getPath($options = [])
    {
        if (in_array(self::LTRIM_PATH, $options)) {
            return ltrim($this->path, '/');
        }

        if (in_array(self::RTRIM_PATH, $options)) {
            return rtrim($this->path, '/');
        }

        return $this->path;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function getQueryParam($key)
    {
        $key = crc32($key);

        if (false === array_key_exists($key, $this->query)) {
            return null;
        }

        return $this->query[$key]['value'];
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return rawurldecode($this->user);
    }

    /**
     * @param string $filename
     */
    public function modifyFilename($filename)
    {
        if ($this->isDir()) {
            $this->path = $this->path . $filename;
        } else {
            if ($this->getDirName() === '') {
                $this->path = $filename;
            } else {
                $this->path = $this->getDirName() . $filename;
            }
        }
    }

    /**
     *
     * @return string
     */
    public function getDirName()
    {
        if ($this->isDir()) {
            return $this->path;
        } elseif (strrpos($this->path, '/') === false) {
            return '';
        } else {
            return substr($this->path, 0, strrpos($this->path, '/') + 1);
        }
    }

    /**
     * @param string $password
     */
    public function modifyPass($password)
    {
        $this->pass = rawurlencode($password);
    }

    /**
     * @todo test this method extensively
     * @todo make this class immutable
     * @param string $path
     */
    public function modifyPath($path)
    {
        $this->path = $path;
    }

    /**
     * @param string $user
     */
    public function modifyUser($user)
    {
        $this->user = rawurlencode($user);
    }

    /**
     * @param boolean $urlEncode
     * @return string
     */
    public function parseBaseUrl($urlEncode = false)
    {
        $url = $this->parseUrl([
            Url::SCHEME, Url::HOST, Url::PORT, Url::USER, Url::PASS, Url::PATH
        ], $urlEncode);

        $filename = $this->getFilename();

        if ($urlEncode === true) {
            $filename = rawurlencode($filename);
        }

        return str_replace($filename, '', $url);
    }

    /**
     * @return boolean
     */
    public function isAbsolutePath()
    {
        return $this->path{0}
        === '/';
    }

    /**
     * @return boolean
     */
    public function isRelativePath()
    {
        return $this->path{0}
        !== '/';
    }
}
