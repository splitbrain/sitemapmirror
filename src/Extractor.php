<?php

namespace splitbrain\sitemapmirror;

use Psr\Log\LoggerInterface;

abstract class Extractor
{

    /** @var LoggerInterface */
    protected $logger;
    /** @var string[] List of URLs found in the document */
    protected $urls = [];
    /** @var string */
    protected $requesturl;
    /** @var string */
    protected $data;

    /**
     * @param LoggerInterface $logger
     * @param string $url The URL that was downloaded
     * @param string $data The data of that URL
     */
    public function __construct(LoggerInterface $logger, $url, $data)
    {
        $this->logger = $logger;
        $this->requesturl = $url;
        $this->data = $data;
    }

    /**
     * Save the data
     *
     * @param string $outdir
     * @return void
     */
    abstract public function save($outdir);

    protected function makeInternalUrl($requesturl, $url)
    {
        if (preg_match('/^\w+:\/\//', $url)) return ''; //external

        if ($url[0] == '/') {
            // absolute url
            return $this->makeSimpleUrl($requesturl) . $url;
        } else {
            // relative url
            return $this->makeSimpleUrl($requesturl, true) . $url;
        }

    }

    protected function makeSimpleUrl($url, $keeppath = false)
    {
        $parsed_url = parse_url($url);
        $scheme = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass = isset($parsed_url['pass']) ? ':' . $parsed_url['pass'] : '';
        $pass = ($user || $pass) ? "$pass@" : '';
        if ($keeppath) {
            $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        } else {
            $path = '/';
        }
        return "$scheme$user$pass$host$port$path";
    }

    /**
     * Get any URLs found in the document
     * @return string[]
     */
    public function getUrls()
    {
        return $this->urls;
    }
}
