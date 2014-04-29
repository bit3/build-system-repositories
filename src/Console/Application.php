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

namespace ContaoCommunityAlliance\BuildSystem\Repositories\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputOption;

abstract class Application extends BaseApplication
{
	public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
	{
		parent::__construct($name, $version);
		$this->getHelperSet()->set(new ConfigurationHelper());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefinition()
	{
		$inputDefinition = parent::getDefinition();
		$inputDefinition->setArguments();

		$inputDefinition->addOption(
			new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to the configuration file')
		);
		$inputDefinition->addOption(
			new InputOption('log', 'l', InputOption::VALUE_REQUIRED, 'Log into a file')
		);
		$inputDefinition->addOption(
			new InputOption('log-level', 'L', InputOption::VALUE_REQUIRED, 'The log level (emergency, alert, critical, error, warning, notice, info, debug)', 'info')
		);

		return $inputDefinition;
	}
}
