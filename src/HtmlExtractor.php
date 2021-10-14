<?php

namespace splitbrain\sitemapmirror;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Options;
use Psr\Log\LoggerInterface;

/**
 * Handles HTML files
 *
 * We save all HTML as a directory with an index.html
 * @todo This works great for Grav, but might need rewriting to avoid breaking relative references?
 */
class HtmlExtractor extends Extractor
{

    /** @inheritDoc */
    public function __construct(LoggerInterface $logger, $url, $data)
    {
        parent::__construct($logger, $url, $data);

        $this->findURLs();
    }

    /** @inheritDoc */
    public function save($outdir)
    {
        $file = parse_url($this->requesturl, PHP_URL_PATH);
        if (!file_exists($outdir . '/' . $file)) {
            mkdir($outdir . '/' . $file, 0777, true);
        }
        $file = $outdir . '/' . $file . '/index.html';
        file_put_contents($file, $this->data);

        $this->findURLs($this->requesturl, $this->data);
    }

    /**
     * Find elements that can have URLs and extract them
     */
    protected function findURLs()
    {
        $dom = new Dom();
        $dom->setOptions((new Options())->setRemoveScripts(false));

        try {
            $dom->loadStr($this->data);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        // all the links (the sitemap may not be complete) and also stylesheets
        $elements = $dom->find('[href]');
        foreach ($elements as $element) {
            /** @var Dom\Node\HtmlNode $element */
            $newurl = $this->makeInternalUrl($this->requesturl, $element->getAttribute('href'));
            if ($newurl) $this->urls[] = $newurl;
        }

        // images and everything that has a src
        $elements = $dom->find('[src]');
        foreach ($elements as $element) {
            /** @var Dom\Node\HtmlNode $element */
            $newurl = $this->makeInternalUrl($this->requesturl, $element->getAttribute('src'));
            if ($newurl) $this->urls[] = $newurl;
        }

        // inline style attributes can have URLs too
        $elements = $dom->find('[style]');
        foreach ($elements as $element) {
            /** @var Dom\Node\HtmlNode $element */
            $style = '.inline{' . $element->getAttribute('style') . '}';

            $cssparser = new CssExtractor($this->logger, $this->requesturl, $style);
            $this->urls = array_merge($this->urls, $cssparser->getUrls());
        }
    }
}
