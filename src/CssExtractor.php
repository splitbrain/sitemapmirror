<?php

namespace splitbrain\sitemapmirror;

use Psr\Log\LoggerInterface;
use Sabberworm\CSS\Property\Import;
use Sabberworm\CSS\Value\URL;

class CssExtractor extends GenericExtractor
{
    public function __construct(LoggerInterface $logger, $url, $data)
    {
        parent::__construct($logger, $url, $data);

        // get the urls and adjust them
        $urls = $this->parseUrls($data);
        foreach ($urls as $newurl) {
            $newurl = (string)$newurl;
            $newurl = trim($newurl, '"\'');
            $newurl = $this->makeInternalUrl($url, $newurl);
            if ($newurl) $this->urls[] = $newurl;
        }
    }

    /**
     * Extract all URLs from the given CSS
     *
     * @param string $data
     * @return array
     */
    protected function parseUrls($data)
    {
        $parser = new \Sabberworm\CSS\Parser($data);
        $cssDocument = $parser->parse();
        $all = $cssDocument->getAllValues(null, true);

        $urls = [];

        foreach ($all as $value) {
            if (is_a($value, URL::class)) {
                /** @var URL $value */
                $urls[] = $value->getURL();
            } elseif (is_a($value, Import::class)) {
                /** @var Import $value */
                $urls[] = $value->getLocation()->getURL();
            }
        }

        return $urls;
    }
}
