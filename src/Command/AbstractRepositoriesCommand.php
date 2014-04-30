<?php

/**
 * This file is part of the Contao Community Alliance Build System tools.
 *
 * @copyright 2014 Contao Community Alliance <https://c-c-a.org>
 * @author    Tristan Lins <t.lins@c-c-a.org>
 * @package   contao-community-alliance/build-system-repositories
 * @license   MIT
 * @link      https://c-c-a.org
 */

namespace ContaoCommunityAlliance\BuildSystem\Repositories\Command;

use ContaoCommunityAlliance\BuildSystem\NoOpLogger;
use ContaoCommunityAlliance\BuildSystem\Repositories\Configuration;
use ContaoCommunityAlliance\BuildSystem\Repositories\Console\ConfigurationHelperInterface;
use ContaoCommunityAlliance\BuildSystem\Repositories\Console\RepositoriesManagerHelperInterface;
use ContaoCommunityAlliance\BuildSystem\Repositories\Environment;
use ContaoCommunityAlliance\BuildSystem\Repositories\Manager;
use ContaoCommunityAlliance\BuildSystem\Repositories\PlaceholderReplacer;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\CompoundProvider;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\Repository;
use Guzzle\Http\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Synchronize the repositories.
 */
abstract class AbstractRepositoriesCommand extends Command
{
	/**
	 * @var Environment
	 */
	protected $environment;

	/**
	 * @param Environment $environment
	 */
	public function setEnvironment(Environment $environment)
	{
		$this->environment = $environment;
		return $this;
	}

	/**
	 * @return Environment
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	protected function configure()
	{
		$this->addOption(
			'owner',
			'o',
			InputOption::VALUE_REQUIRED,
			'Sync only repositories from a specific owner (fnmatch wildcard allowed).'
		);
		$this->addOption(
			'repository',
			'r',
			InputOption::VALUE_REQUIRED,
			'Sync only repositories from a specific owner (fnmatch wildcard allowed).'
		);
		$this->addOption(
			'provider',
			'p',
			InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
			'Only use specific provider.'
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var HelperSet $helperSet */
		$helperSet = $this->getHelperSet();

		if (!$this->environment) {
			$this->environment = new Environment();
		}

		if (!$this->environment->hasApplication()) {
			$this->environment->setApplication($this->getApplication());
		}

		if (!$this->environment->hasConfiguration()) {
			if ($helperSet->has('ccabs:repositories:configuration')) {
				/** @var ConfigurationHelperInterface $helper */
				$helper              = $helperSet->get('ccabs:repositories:configuration');
				$this->environment->setConfiguration($helper->getConfiguration($this->environment));
			}
			else {
				throw new \RuntimeException('Configuration is not provided');
			}
		}

		// TODO use a helper to get this instance
		if (!$this->environment->hasPlaceholderReplacer()) {
			$this->environment->setPlaceholderReplacer(new PlaceholderReplacer());
		}

		$this->environment->setInput($input);
		$this->environment->setOutput($output);

		if ($output instanceof ConsoleOutputInterface) {
			$this->environment->setErrorOutput($output->getErrorOutput());
		}
		else {
			$this->environment->setErrorOutput($output);
		}

		if ($input->hasOption('log') && $input->hasParameterOption('log')) {
			$logFile = $input->getOption('log');
			$logLevel = $input->getOption('log-level');

			$logger = new Logger('ccabs:repositories', array(new StreamHandler($logFile, $logLevel)));
			$this->environment->setLogger($logger);
		}

		$ownerName      = $input->getOption('owner');
		$repositoryName = $input->getOption('repository');

		$providers = (array) $input->getOption('provider');

		if (count($providers)) {
			$compoundProvider = new CompoundProvider($this->environment);

			foreach ($this->environment->getConfiguration()->getProvider()->getProviders() as $provider) {
				if (in_array($provider->getName(), $providers)) {
					$compoundProvider->addProvider($provider);
				}
			}

			$repositories = $compoundProvider->listAll();
		}
		else {
			$repositories = $this->environment->getConfiguration()->getProvider()->listAll();
		}

		if ($ownerName || $repositoryName) {
			$repositories = array_filter(
				$repositories,
				function (Repository $repository) use ($ownerName, $repositoryName) {
					return (
						!$ownerName ||
						fnmatch($ownerName, $repository->getOwner())
					) && (
						!$repositoryName ||
						fnmatch($repositoryName, $repository->getName())
					);
				}
			);
		}

		$this->executeOn($repositories, $input, $output);
	}

	abstract protected function executeOn(array $repositories, InputInterface $input, OutputInterface $output);
}
