<?php

namespace splitbrain\sitemapmirror;

use Psr\Log\LoggerInterface;

/**
 * Manages all Downloads
 */
class Downloader
{
    /** @var string where to save files */
    protected $outdir;
    /** @var \GuzzleHttp\Client HTTP Client */
    protected $guzzle;
    /** @var LoggerInterface the CLI logger */
    protected $logger;
    /** @var string[] URLs still to process */
    protected $tofetch = [];
    /** @var string[] URLs already processed */
    protected $fetched = [];

    /**
     * @param LoggerInterface $logger
     * @param string $outdir Where to save files
     */
    public function __construct(LoggerInterface $logger, $outdir)
    {
        $this->guzzle = new  \GuzzleHttp\Client();
        $this->logger = $logger;
        $this->outdir = $outdir;
    }

    /**
     * Start the download process using the given URLs
     * @param string[] $urls
     */
    public function download($urls)
    {
        $this->tofetch = $urls;
        while ($url = array_shift($this->tofetch)) {
            $this->fetch($url);
        }
    }

    /**
     * Download a process a single URL
     *
     * Depending on the mime type a different Extractor is run. Extractors will save the content
     * and might return additional URLs which will be enqueued.
     *
     * @param string $url
     */
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
