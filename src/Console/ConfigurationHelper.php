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

use ContaoCommunityAlliance\BuildSystem\Repositories\Configurator\YamlConfigurator;
use ContaoCommunityAlliance\BuildSystem\Repositories\Environment;
use Symfony\Component\Console\Helper\InputAwareHelper;
use Symfony\Component\Console\Input\InputAwareInterface;
use Symfony\Component\Console\Input\InputInterface;

class ConfigurationHelper extends InputAwareHelper implements ConfigurationHelperInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'ccabs:repositories:configuration';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConfiguration(Environment $environment)
	{
		/** @var InputInterface $input */
		$input = $this->input;

		$file      = $input->getOption('config');
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		switch ($extension) {
			case 'yml':
			case 'yaml':
				$configurator  = new YamlConfigurator();
				$configuration = $configurator->parseConfiguration($environment, $file);
				break;

			default:
				throw new \InvalidArgumentException(sprintf('Configuration file "%s" is not supported.', basename($file)));
		}

		return $configuration;
	}
}
