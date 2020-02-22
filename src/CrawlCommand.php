<?php
namespace JHodges\Sitemap;

use GuzzleHttp\RequestOptions;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CrawlCommand extends Command
{
    protected function configure()
    {
        $this->setName('crawl')
            ->setDescription('Crawl and generate sitemap for the website.')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'The url to check'
            )->addOption(
                'found-on',
                'f',
                InputOption::VALUE_NONE,
                'Display found on URLs'
            );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseUrl = $input->getArgument('url');
        $crawler=new Crawler();
        $crawler->crawl($baseUrl);

        foreach($crawler->getResults() as $url=>$result){
                $output->writeln("{$result['code']} {$url}");
                if($input->getOption('found-on')){
                    foreach($result['foundOn'] as $url=>$count){
                        $output->writeln("    -> ($count) $url");
                    }
                }
        }

        return 0;
    }
}
