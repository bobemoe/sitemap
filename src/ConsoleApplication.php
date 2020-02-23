<?php

namespace JHodges\Sitemap;

use Symfony\Component\Console\Application;

class ConsoleApplication extends Application
{
    public function __construct()
    {
        error_reporting(-1);

        parent::__construct('Sitempa', '0.1.0');

        $this->add(new CrawlCommand());
    }

    public function getLongVersion()
    {
        return parent::getLongVersion().' by <comment>JHodges</comment>';
    }
}
