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
 * Provider that merge informations from multiple providers.
 */
class CompoundProvider implements ProviderInterface
{
	/**
	 * @var ProviderInterface[]
	 */
	protected $providers = array();

	/**
	 * @param array $providers
	 */
	public function __construct(array $providers = array())
	{
		$this->providers = $providers;
	}

	public function clearProviders()
	{
		$this->providers = array();
	}

	/**
	 * @param ProviderInterface $provider
	 */
	public function addProvider(ProviderInterface $provider)
	{
		$hash                   = spl_object_hash($provider);
		$this->providers[$hash] = $provider;
		return $this;
	}

	/**
	 * @param ProviderInterface $provider
	 */
	public function removeProvider(ProviderInterface $provider)
	{
		$hash = spl_object_hash($provider);
		unset($this->providers[$hash]);
		return $this;
	}

	public function addProviders(array $providers)
	{
		foreach ($providers as $provider) {
			$this->addProvider($provider);
		}
		return $this;
	}

	/**
	 * @param \ContaoCommunityAlliance\BuildSystem\Repositories\Provider\ProviderInterface[] $providers
	 */
	public function setProviders(array $providers)
	{
		$this->clearProviders();
		$this->addProviders($providers);
		return $this;
	}

	/**
	 * @return \ContaoCommunityAlliance\BuildSystem\Repositories\Provider\ProviderInterface[]
	 */
	public function getProviders()
	{
		return $this->providers;
	}

	/**
	 * {@inheritdoc}
	 */
	public function listAll()
	{
		$repositories = array();

		foreach ($this->providers as $provider) {
			$repositories = array_merge(
				$repositories,
				$provider->listAll()
			);
		}

		return $repositories;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($owner, $name)
	{
		foreach ($this->providers as $provider) {
			try {
				$repository = $provider->get($owner, $name);

				if ($repository) {
					return $repository;
				}
			}
			catch (\Exception $e) {
				// silently ignore
			}
		}

		return null;
	}
}
