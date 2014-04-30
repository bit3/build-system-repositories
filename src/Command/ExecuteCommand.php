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
use ContaoCommunityAlliance\BuildSystem\Repositories\Console\RepositoriesManagerHelperInterface;
use ContaoCommunityAlliance\BuildSystem\Repositories\Exception\IncompleteConfigurationException;
use ContaoCommunityAlliance\BuildSystem\Repositories\Exception\NotSynchronizedRepository;
use ContaoCommunityAlliance\BuildSystem\Repositories\Manager;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\Repository;
use ContaoCommunityAlliance\BuildSystem\Repository\GitRepository;
use Guzzle\Http\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Synchronize the repositories.
 */
class ExecuteCommand extends AbstractRepositoriesCommand
{
	protected function configure()
	{
		$this->setName('ccabs:repositories:execute');
		parent::configure();
		$this->addOption('sync', 's', InputOption::VALUE_NONE, 'Synchronise before execute the actions.');
	}

	protected function executeOn(array $repositories, InputInterface $input, OutputInterface $output)
	{
		$preAction = $this->environment->getConfiguration()->getPreAction();
		if ($preAction) {
			$preAction->run($this->environment);
		}

		$this->executeEach($repositories, $input, $output);

		$postAction = $this->environment->getConfiguration()->getPostAction();
		if ($postAction) {
			$postAction->run($this->environment);
		}
	}

	public function executeAll(InputInterface $input, OutputInterface $output)
	{
		$this->executeEach($this->environment->getConfiguration()->getProvider()->listAll(), $input, $output);
	}

	public function executeEach(array $repositories, InputInterface $input, OutputInterface $output)
	{
		$synchronizedPaths = array();
		foreach ($repositories as $repository) {
			$this->executeActions($repository, $input, $output, $synchronizedPaths);
		}
	}

	public function executeActions(Repository $repository, InputInterface $input, OutputInterface $output, array &$synchronizedPaths = array())
	{
		$this->environment->setRepository($repository);

		$path = $this->environment->getPlaceholderReplacer()->replace(
			$this->environment->getConfiguration()->getStoragePath() . DIRECTORY_SEPARATOR . $this->environment->getConfiguration()->getDirectoryScheme(),
			$this->environment
		);

		$this->environment->setPath($path);

		switch ($repository->getType()) {
			case Repository::TYPE_GIT:
				$vcs = new GitRepository($path);

				if ($input->getOption('sync')) {
					$sync = new SyncCommand();
					$sync->setEnvironment($this->environment);
					$sync->sync($repository, $input, $output, $synchronizedPaths);
				}

				if (!$vcs->isInitialized()) {
					throw new NotSynchronizedRepository(sprintf(
						'The repository %s/%s is not synchronized',
						$repository->getOwner(),
						$repository->getName()
					));
				}

				$vcs->checkout()->force()->execute($repository->getRealRef());
				break;

			default:
				// TODO
				throw new \Exception('Incomplete implementation');
		}

		$action = $this->environment->getConfiguration()->getAction();

		if (!$action) {
			throw new IncompleteConfigurationException('No actions configured');
		}

		if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
			$output->writeln(
				sprintf(
					' * <info>ccabs:repositories:execute</info> run action on repository <comment>%s</comment>',
					$path
				)
			);
		}

		$this->environment->setVcs($vcs);

		$action->run($this->environment);

		$this->environment->setVcs(null);
		$this->environment->setPath(null);
		$this->environment->setRepository(null);
	}
}
