<?php

namespace splitbrain\sitemapmirror;

class GenericExtractor extends Extractor
{

    /** @inheritDoc */
    public function save($outdir)
    {
        $file = parse_url($this->requesturl, PHP_URL_PATH);
        $dir = dirname($file);
        if (!file_exists($outdir . '/' . $dir)) {
            mkdir($outdir . '/' . $dir, 0777, true);
        }
        $file = $outdir . '/' . $file;
        file_put_contents($file, $this->data);
    }
}
