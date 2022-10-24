<?php

namespace splitbrain\sitemapmirror;

use splitbrain\phpcli\Options;
use splitbrain\phpcli\PSR3CLI;
use vipnytt\SitemapParser;

class SiteMapMirror extends PSR3CLI
{
    /** @inheritDoc */
    protected function setup(Options $options)
    {
        $options->setHelp(
            'This is a *very* simple web site copier. It uses a given XML sitemap as a starting point ' .
            'to download all the pages and their assets. It does *no* rewriting of URLs so URLs need ' .
            'to be relative or server absolute.'
        );

        $options->registerArgument('sitemap url', 'The URL to the XML sitemap of the site to copy');
        $options->registerOption('dir', 'The directory to save to. Defaults to a dir named after the server.', 'd', 'directory');
    }

    /**  @inheritDoc */
    protected function main(Options $options)
    {

        $sitemap = ($options->getArgs())[0];
        $outdir = parse_url($sitemap, PHP_URL_HOST);
        $outdir = $options->getOpt('dir', $outdir);

        $parser = new SitemapParser(SitemapParser::DEFAULT_USER_AGENT, ['strict' => false]);
        $parser->parse($sitemap);
        $urls = array_keys($parser->getURLs());

        $dl = new Downloader($this, $outdir);
        $dl->download($urls);
    }
}
