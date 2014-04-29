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

namespace ContaoCommunityAlliance\BuildSystem\Repositories;

use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\Repository;
use ContaoCommunityAlliance\BuildSystem\Repository\GitRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Environment used while processing.
 */
class Environment
{
	/**
	 * @var Application
	 */
	protected $application;

	/**
	 * @var Configuration
	 */
	protected $configuration;

	/**
	 * @var PlaceholderReplacer
	 */
	protected $placeholderReplacer;

	/**
	 * The logger.
	 *
	 * @var null|LoggerInterface
	 */
	protected $logger;

	/**
	 * The input.
	 *
	 * @var null|InputInterface
	 */
	protected $input;

	/**
	 * The output.
	 *
	 * @var null|OutputInterface
	 */
	protected $output;

	/**
	 * The error output.
	 *
	 * @var null|OutputInterface
	 */
	protected $errorOutput;

	/**
	 * The current repository.
	 *
	 * @var null|Repository
	 */
	protected $repository;

	/**
	 * The local repository path.
	 *
	 * @var null|string
	 */
	protected $path;

	/**
	 * The current vcs adapter.
	 *
	 * @var null|GitRepository
	 */
	protected $vcs;

	public function hasApplication()
	{
		return (bool) $this->application;
	}

	/**
	 * @param Application $application
	 */
	public function setApplication(Application $application = null)
	{
		$this->application = $application;
		return $this;
	}

	/**
	 * @return Application
	 */
	public function getApplication()
	{
		return $this->application;
	}

	public function hasConfiguration()
	{
		return (bool) $this->configuration;
	}

	/**
	 * @param Configuration $configuration
	 */
	public function setConfiguration(Configuration $configuration = null)
	{
		$this->configuration = $configuration;
		return $this;
	}

	/**
	 * @return Configuration
	 */
	public function getConfiguration()
	{
		return $this->configuration;
	}

	public function hasPlaceholderReplacer()
	{
		return (bool) $this->placeholderReplacer;
	}

	/**
	 * @param PlaceholderReplacer $placeholderReplacer
	 */
	public function setPlaceholderReplacer(PlaceholderReplacer $placeholderReplacer = null)
	{
		$this->placeholderReplacer = $placeholderReplacer;
		return $this;
	}

	/**
	 * @return PlaceholderReplacer
	 */
	public function getPlaceholderReplacer()
	{
		return $this->placeholderReplacer;
	}

	public function hasLogger()
	{
		return (bool) $this->logger;
	}

	/**
	 * @param null|LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger = null)
	{
		$this->logger = $logger;
		return $this;
	}

	/**
	 * @return null|LoggerInterface
	 */
	public function getLogger()
	{
		return $this->logger;
	}

	public function hasInput()
	{
		return (bool) $this->input;
	}

	/**
	 * @param null|InputInterface $input
	 */
	public function setInput(InputInterface $input = null)
	{
		$this->input = $input;
		return $this;
	}

	/**
	 * @return null|InputInterface
	 */
	public function getInput()
	{
		return $this->input;
	}

	public function hasOutput()
	{
		return (bool) $this->output;
	}

	/**
	 * @param null|OutputInterface $output
	 */
	public function setOutput(OutputInterface $output = null)
	{
		$this->output = $output;
		return $this;
	}

	/**
	 * @return null|OutputInterface
	 */
	public function getOutput()
	{
		return $this->output;
	}

	public function hasErrorOutput()
	{
		return (bool) $this->errorOutput;
	}

	/**
	 * @param null|OutputInterface $error
	 */
	public function setErrorOutput(OutputInterface $error = null)
	{
		$this->errorOutput = $error;
		return $this;
	}

	/**
	 * @return null|OutputInterface
	 */
	public function getErrorOutput()
	{
		return $this->errorOutput;
	}

	public function hasRepository()
	{
		return (bool) $this->repository;
	}

	/**
	 * @param Repository|null $repository
	 */
	public function setRepository(Repository $repository = null)
	{
		$this->repository = $repository;
		return $this;
	}

	/**
	 * @return Repository|null
	 */
	public function getRepository()
	{
		return $this->repository;
	}

	public function hasPath()
	{
		return (bool) $this->path;
	}

	/**
	 * @param null|string $path
	 */
	public function setPath($path)
	{
		$this->path = empty($path) ? null : (string) $path;
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getPath()
	{
		return $this->path;
	}

	public function hasVcs()
	{
		return (bool) $this->vcs;
	}

	/**
	 * @param GitRepository|null $vcs
	 */
	public function setVcs($vcs)
	{
		$this->vcs = $vcs;
		return $this;
	}

	/**
	 * @return GitRepository|null
	 */
	public function getVcs()
	{
		return $this->vcs;
	}
}
