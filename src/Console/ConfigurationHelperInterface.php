<?php

/**
 * <project name>
 *
 * PHP Version 5.3
 *
 * @copyright  bit3 UG 2013
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @package    <project>
 * @license    LGPL-3.0+
 * @link       <link>
 */
namespace ContaoCommunityAlliance\BuildSystem\Repositories\Console;

use ContaoCommunityAlliance\BuildSystem\Repositories\Configuration;
use ContaoCommunityAlliance\BuildSystem\Repositories\Environment;

interface ConfigurationHelperInterface
{
	/**
	 * Provide the configuration for the repositories manager.
	 *
	 * @param Environment $environment The environment.
	 *
	 * @return Configuration
	 */
	public function getConfiguration(Environment $environment);
}