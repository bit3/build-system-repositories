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
class SyncCommand extends AbstractRepositoriesCommand
{
	protected function configure()
	{
		$this->setName('ccabs:repositories:sync');
		parent::configure();
	}

	protected function executeOn(array $repositories, InputInterface $input, OutputInterface $output)
	{
		$this->syncEach($repositories, $input, $output);
	}

	public function syncAll(InputInterface $input, OutputInterface $output)
	{
		$this->syncEach($this->environment->getProvider()->listAll(), $input, $output);
	}

	public function syncEach(array $repositories, InputInterface $input, OutputInterface $output)
	{
		$synchronizedPaths = array();
		foreach ($repositories as $repository) {
			$this->sync($repository, $input, $output, $synchronizedPaths);
		}
	}

	/**
	 * @param Repository      $repository        The repository to sync.
	 * @param InputInterface  $input             The input options.
	 * @param OutputInterface $output            The output options.
	 * @param array           $synchronizedPaths For internal use only, used by {@link
	 *                                           ContaoCommunityAlliance\BuildSystem\Repositories\Command\SyncCommand::syncEach()
	 *                                           syncEach()} to prevent syncing the same repository multiple times.
	 *
	 * @throws \Exception
	 */
	public function sync(
		Repository $repository,
		InputInterface $input,
		OutputInterface $output,
		array &$synchronizedPaths = array()
	) {
		$path = $this->environment->getPlaceholderReplacer()->replace(
			$this->environment->getConfiguration()->getStoragePath() . DIRECTORY_SEPARATOR . $this->environment->getConfiguration()->getDirectoryScheme(),
			$this->environment
		);

		if (in_array($path, $synchronizedPaths)) {
			return;
		}

		$filesystem = new Filesystem();
		if (!$filesystem->exists($path)) {
			if ($output->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG) {
				$output->writeln(
					sprintf(
						' * <info>ccabs:repositories:sync</info> create repository directory <comment>%s</comment>',
						$path
					)
				);
			}
			$filesystem->mkdir($path);
		}

		switch ($repository->getType()) {
			case Repository::TYPE_GIT:
				$vcs = new GitRepository($path);

				if (!$vcs->isInitialized()) {
					if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
						$output->writeln(
							sprintf(
								' * <info>ccabs:repositories:sync</info> clean repository <comment>%s</comment>',
								$path
							)
						);
					}

					$vcs->cloneRepository($repository->getReadUrl(), null, $repository->getRemoteName());
					$vcs->remoteSetPushUrl($repository->getWriteUrl());
				}
				else {
					if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
						$output->writeln(
							sprintf(
								' * <info>ccabs:repositories:sync</info> sync repository <comment>%s</comment>',
								$path
							)
						);
					}

					if (in_array($repository->getRemoteName(), $vcs->listRemotes())) {
						$vcs->remoteSetFetchUrl($repository->getReadUrl(), $repository->getRemoteName());
					}
					else {
						$vcs->remoteAdd($repository->getReadUrl(), $repository->getRemoteName());
					}
					$vcs->remoteSetPushUrl($repository->getWriteUrl(), $repository->getRemoteName());
					$vcs->remoteFetch(true, $repository->getRemoteName());
				}
				break;

			default:
				// TODO
				throw new \Exception('Incomplete implementation');
		}
	}
}
