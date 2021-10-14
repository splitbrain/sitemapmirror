<?php

namespace splitbrain\sitemapmirror;

use Psr\Log\LoggerInterface;

class Downloader
{

    protected $outdir;
    protected $guzzle;
    protected $logger;
    protected $tofetch = [];
    protected $fetched = [];

    public function __construct(LoggerInterface $logger, $outdir)
    {
        $this->guzzle = new  \GuzzleHttp\Client();
        $this->logger = $logger;
        $this->outdir = $outdir;
    }

    public function download($urls)
    {
        $this->tofetch = $urls;
        while ($url = array_shift($this->tofetch)) {
            $this->fetch($url);
        }
    }

    protected function fetch($url)
    {
        $this->logger->info($url);
        $response = $this->guzzle->request('GET', $url);
        if ($response->getStatusCode() !== 200) {
            $this->logger->warning("Non 200 status for $url");
            return;
        }
        $this->fetched[] = $url;

        $type = $response->getHeader('content-type')[0];
        list($type) = explode(';', $type);

        switch ($type) {
            case 'text/html':
                $extractorClass = HtmlExtractor::class;
                break;
            case 'text/css':
                $extractorClass = CssExtractor::class;
                break;
            default:
                $extractorClass = GenericExtractor::class;
        }

        $extractor = new $extractorClass($this->logger, $url, $response->getBody());
        $extractor->save($this->outdir);
        $this->queueUrls($extractor->getUrls());
    }

    /**
     * Add the given URLs to the download queue
     *
     * @param string[] $urls
     */
    protected function queueUrls($urls)
    {
        foreach ($urls as $url) {
            if (in_array($url, $this->tofetch)) continue;
            if (in_array($url, $this->fetched)) continue;
            array_unshift($this->tofetch, $url);
        }
    }

}
