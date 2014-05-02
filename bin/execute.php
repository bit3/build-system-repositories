#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use ContaoCommunityAlliance\BuildSystem\Repositories\Command\ExecuteCommand;
use ContaoCommunityAlliance\BuildSystem\Repositories\Console\Application;

set_error_handler(
	function ($errno, $errstr, $errfile, $errline ) {
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
);

class ExecuteApplication extends Application
{
	/**
	 * {@inheritdoc}
	 */
	protected function getCommandName(InputInterface $input)
	{
		return 'ccabs:repositories:execute';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getDefaultCommands()
	{
		$defaultCommands   = parent::getDefaultCommands();
		$defaultCommands[] = new ExecuteCommand();
		return $defaultCommands;
	}
}

$application = new ExecuteApplication();
$application->run();
