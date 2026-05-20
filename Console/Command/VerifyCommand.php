<?php

declare(strict_types=1);

namespace ETechFlow\AccountLinksManager\Console\Command;

use ETechFlow\AccountLinksManager\Model\Config;
use ETechFlow\AccountLinksManager\Model\LicenseValidator;
use ETechFlow\AccountLinksManager\Model\Source\AvailableLinks;
use ETechFlow\AccountLinksManager\Model\Source\Mode;
use ETechFlow\AccountLinksManager\Plugin\NavigationPlugin;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * `bin/magento etechflow:alm:verify`
 *
 * Headless end-to-end check of ETechFlow_AccountLinksManager. Confirms
 * classes resolve via DI, the licence validator evaluates, the config
 * reads, and the available-links source returns its option list.
 */
class VerifyCommand extends Command
{
    public function __construct(
        private readonly AppState $appState,
        private readonly ObjectManagerInterface $objectManager,
        private readonly LicenseValidator $licenseValidator,
        private readonly Config $config,
        private readonly AvailableLinks $availableLinks
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('etechflow:alm:verify')
            ->setDescription('Headless end-to-end check of the ETechFlow Account Links Manager module.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->appState->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->appState->setAreaCode(Area::AREA_FRONTEND);
        }

        $output->writeln('<info>=== ETechFlow Account Links Manager verify ===</info>');
        $output->writeln('');

        $allPassed = true;

        try {
            $this->step($output, '1. LicenseValidator evaluates without throwing');
            $host = $this->licenseValidator->getCurrentHost();
            $valid = $this->licenseValidator->isValid();
            $dev = $this->licenseValidator->isDevHost();
            $this->pass($output, sprintf(
                'host=%s; dev_host=%s; valid=%s',
                $host !== '' ? $host : '(empty)',
                $dev ? 'yes' : 'no',
                $valid ? 'yes' : 'no'
            ));

            $this->step($output, '2. Config.isEnabled() returns a boolean without throwing');
            $enabled = $this->config->isEnabled();
            $mode = $this->config->getMode();
            $this->pass($output, sprintf('enabled=%s mode=%s', $enabled ? 'yes' : 'no', $mode));

            $this->step($output, '3. AvailableLinks source returns the standard option list');
            $options = $this->availableLinks->toOptionArray();
            if (count($options) < 10) {
                throw new \RuntimeException(sprintf(
                    'Expected the source to return >= 10 options (full core + AC list); got %d',
                    count($options)
                ));
            }
            $this->pass($output, sprintf('%d link options exposed', count($options)));

            $this->step($output, '4. Mode source returns both expected options');
            $modeOptions = (new Mode())->toOptionArray();
            $values = array_column($modeOptions, 'value');
            if (!in_array(Mode::HIDE_SELECTED, $values, true) || !in_array(Mode::SHOW_ONLY, $values, true)) {
                throw new \RuntimeException('Mode source missing one of the expected values');
            }
            $this->pass($output);

            $this->step($output, '5. NavigationPlugin resolves via DI');
            $plugin = $this->objectManager->get(NavigationPlugin::class);
            if (!$plugin instanceof NavigationPlugin) {
                throw new \RuntimeException('Plugin DI returned wrong type');
            }
            $this->pass($output);

            $this->step($output, '6. getManagedBlockNames combines multi-select + extra-blocks textarea');
            $managed = $this->config->getManagedBlockNames();
            $this->pass($output, sprintf('%d managed block names', count($managed)));

            $output->writeln('');
            $output->writeln('<info>✅ ALL CHECKS PASSED. v1.0.0 verified.</info>');
        } catch (\Throwable $e) {
            $allPassed = false;
            $output->writeln('');
            $output->writeln('<error>❌ FAIL: ' . $e->getMessage() . '</error>');
            $output->writeln('<error>at ' . $e->getFile() . ':' . $e->getLine() . '</error>');
        }

        return $allPassed ? Command::SUCCESS : Command::FAILURE;
    }

    private function step(OutputInterface $output, string $label): void
    {
        $output->write('  ' . $label . ' ... ');
    }

    private function pass(OutputInterface $output, string $detail = ''): void
    {
        $output->writeln('<info>OK</info>' . ($detail !== '' ? " ({$detail})" : ''));
    }
}
