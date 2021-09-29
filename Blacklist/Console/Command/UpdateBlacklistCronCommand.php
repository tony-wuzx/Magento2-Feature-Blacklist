<?php

declare(strict_types=1);

namespace Zhixing\Blacklist\Console\Command;

use Zhixing\Blacklist\Cron\UpdateBlacklist;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateBlacklistCronCommand
 *
 * @package Zhixing\Blacklist\Console\Command
 */
class UpdateBlacklistCronCommand extends Command
{
    const NAME = 'zhixing:blacklist:update:cron';

    /**
     * @var State
     */
    protected $state;

    /**
     * @var UpdateBlacklist
     */
    protected $updateBlacklist;

    /**
     * UpdateBlacklistCronCommand constructor.
     *
     * @param UpdateBlacklist $updateBlacklist
     * @param State $state
     */
    public function __construct(
        UpdateBlacklist $updateBlacklist,
        State $state
    ) {
        $this->updateBlacklist = $updateBlacklist;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::NAME)
            ->setDescription(
                'trigger cronjob that update blacklist from customer collection'
            );
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);

        try {
            $this->updateBlacklist->execute();
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }
}
