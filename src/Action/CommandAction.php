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

namespace ContaoCommunityAlliance\BuildSystem\Repositories\Action;

use ContaoCommunityAlliance\BuildSystem\Repositories\Environment;
use ContaoCommunityAlliance\BuildSystem\Repositories\Exception\ActionException;
use ContaoCommunityAlliance\BuildSystem\Repositories\PlaceholderReplacer;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\Repository;
use ContaoCommunityAlliance\BuildSystem\Repository\GitRepository;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class CommandAction extends AbstractAction
{
	/**
	 * @var string
	 */
	protected $command;

	/**
	 * @var string[]
	 */
	protected $arguments;

	/**
	 * @var array
	 */
	protected $settings;

	/**
	 * @var PlaceholderReplacer
	 */
	protected $placeholderReplacer;

	function __construct($command, array $arguments, array $settings = array())
	{
		$this->setCommand($command);
		$this->setArguments($arguments);
		$this->setSettings($settings);
	}

	/**
	 * @param mixed $command
	 */
	public function setCommand($command)
	{
		$this->command = (string) $command;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getCommand()
	{
		return $this->command;
	}

	public function addArgument($argument)
	{
		$this->arguments[] = (string) $argument;
	}

	/**
	 * @param string[] $arguments
	 */
	public function setArguments(array $arguments)
	{
		$this->arguments = array();
		foreach ($arguments as $argument) {
			$this->addArgument($argument);
		}
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 * @param array $settings
	 */
	public function setSettings(array $settings)
	{
		$this->settings = $settings;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getSettings()
	{
		return $this->settings;
	}

	/**
	 * @param PlaceholderReplacer $placeholderReplacer
	 */
	public function setPlaceholderReplacer(PlaceholderReplacer $placeholderReplacer)
	{
		$this->placeholderReplacer = $placeholderReplacer;
		return $this;
	}

	/**
	 * @return PlaceholderReplacer
	 */
	public function getPlaceholderReplacer()
	{
		if ($this->placeholderReplacer === null) {
			$this->placeholderReplacer = new PlaceholderReplacer();
		}

		return $this->placeholderReplacer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(Environment $environment)
	{
		if ($this->condition && !$this->condition->accept($this, $environment)) {
			return;
		}

		$placeholderReplacer = $this->getPlaceholderReplacer();

		$parameters = array($placeholderReplacer->replace($this->command, $environment));
		foreach ($this->arguments as $argument) {
			$parameters[] = $placeholderReplacer->replace($argument, $environment);
		}

		$cwd = getcwd();
		if ($this->settings['workingDirectory']) {
			chdir($this->settings['workingDirectory']);
		}
		else {
			chdir($environment->getPath());
		}

		if ($environment->hasLogger()) {
			$environment->getLogger()->debug(
				sprintf(
					'[ccabs:repositories:command-action] forward command %s',
					implode(' ', $parameters)
				)
			);
		}
		if (
			$environment->hasOutput() &&
			$environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
		) {
			$environment->getOutput()->writeln(
				sprintf(
					' * <info>ccabs:repositories:command-action</info> forward command %s',
					implode(' ', $parameters)
				)
			);
		}

		try {
			$forwardInput = new ArrayInput($parameters);
			$environment->getApplication()->run($forwardInput, $environment->getOutput());
			chdir($cwd);
		}
		catch (\Exception $e) {
			chdir($cwd);

			if (!isset($this->settings['ignoreFailure']) || !$this->settings['ignoreFailure']) {
				throw new ActionException('Command action failed', 0, $e);
			}
		}
	}
}
