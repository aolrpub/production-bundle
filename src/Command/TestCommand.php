<?php

namespace Aolr\ProductionBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    protected static $defaultName = 'test:new';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // https://libraries.mit.edu/scholarly/publishing/apis-for-scholarly-resources/
        $output->writeln('test');

        return Command::SUCCESS;
    }
}
