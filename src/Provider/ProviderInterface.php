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

namespace ContaoCommunityAlliance\BuildSystem\Repositories\Provider;

use ContaoCommunityAlliance\BuildSystem\NoOpLogger;
use Guzzle\Http\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Repositories provider.
 */
interface ProviderInterface
{
	/**
	 * List all repositories from this provider.
	 *
	 * @return Repository[]
	 */
	public function listAll();

	/**
	 * Get all repositories by its owner+name combination.
	 *
	 * @param string $owner The repository owner name.
	 * @param string $name The repository name.
	 *
	 * @return Repository[]
	 */
	public function get($owner, $name);
}
