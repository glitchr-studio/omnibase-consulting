<?php

namespace Base\Consulting\Command;

use Base\Consulting\Repository\QuoteRequestRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The quote requests kept no longer than the form's notice says
 * (consulting.retention_months): run it from cron, once a day.
 */
#[AsCommand(name: 'consulting:purge', description: 'Delete the quote requests older than consulting.retention_months.')]
final class PurgeCommand extends Command
{
    public function __construct(
        private readonly QuoteRequestRepository $quotes,
        #[Autowire('%consulting.retention_months%')] private readonly int $months = 36,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $deleted = $this->quotes->purgeOlderThan(new \DateTimeImmutable(sprintf('-%d months', $this->months)));
        (new SymfonyStyle($input, $output))->success(sprintf('%d quote request(s) older than %d months deleted.', $deleted, $this->months));

        return Command::SUCCESS;
    }
}
