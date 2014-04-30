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

namespace ContaoCommunityAlliance\BuildSystem\Repositories\Configurator;

use ContaoCommunityAlliance\BuildSystem\Repositories\Action\CommandAction;
use ContaoCommunityAlliance\BuildSystem\Repositories\Action\CompoundAction;
use ContaoCommunityAlliance\BuildSystem\Repositories\Action\JsonAction;
use ContaoCommunityAlliance\BuildSystem\Repositories\Action\ProcessAction;
use ContaoCommunityAlliance\BuildSystem\Repositories\Condition\AndCondition;
use ContaoCommunityAlliance\BuildSystem\Repositories\Condition\ConjunctionCondition;
use ContaoCommunityAlliance\BuildSystem\Repositories\Condition\FileExistsCondition;
use ContaoCommunityAlliance\BuildSystem\Repositories\Condition\NotCondition;
use ContaoCommunityAlliance\BuildSystem\Repositories\Condition\OrCondition;
use ContaoCommunityAlliance\BuildSystem\Repositories\Condition\ProviderCondition;
use ContaoCommunityAlliance\BuildSystem\Repositories\Configuration;
use ContaoCommunityAlliance\BuildSystem\Repositories\Environment;
use ContaoCommunityAlliance\BuildSystem\Repositories\Exception\IncompleteConfigurationException;
use ContaoCommunityAlliance\BuildSystem\Repositories\Exception\InvalidConfigurationException;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\AccessTokenAuth;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\BasicAuth;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\BitBucketProvider;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\CompoundProvider;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\GithubProvider;
use Symfony\Component\Yaml\Yaml;

/**
 * Configurator use a yaml configuration file.
 */
class YamlConfigurator
{
	/**
	 * @param string $file
	 *
	 * @return Configuration
	 */
	public function parseConfiguration(Environment $environment, $file)
	{
		if (!is_file($file)) {
			throw new \InvalidArgumentException(sprintf('File %s does not exist', $file));
		}

		$configuration = new Configuration();
		$parser        = new Yaml();
		$array         = $parser->parse($file);

		if (!isset($array['config'])) {
			throw new IncompleteConfigurationException('Mandatory configuration field "config" missing');
		}
		if (!isset($array['config']['storage'])) {
			throw new IncompleteConfigurationException('Mandatory configuration field "config.storage" missing');
		}
		if (!isset($array['providers'])) {
			throw new IncompleteConfigurationException('Mandatory configuration field "providers" missing');
		}

		$configuration->setStoragePath($array['config']['storage']);

		if (isset($array['config']['directory-scheme'])) {
			$configuration->setDirectoryScheme($array['config']['directory-scheme']);
		}

		$compoundProvider = new CompoundProvider($environment);
		$configuration->setProvider($compoundProvider);

		foreach ($array['providers'] as $providerName => $providerConfiguration) {
			if (!isset($providerConfiguration['type'])) {
				throw new IncompleteConfigurationException(sprintf(
					'Mandatory configuration field "providers[%s].type" missing',
					$providerName
				));
			}

			switch ($providerConfiguration['type']) {
				case 'bitbucket':
					if (!isset($providerConfiguration['owner'])) {
						throw new IncompleteConfigurationException(sprintf(
							'Mandatory configuration field "providers[%s].owner" missing',
							$providerName
						));
					}

					$provider = new BitBucketProvider($environment, $providerName, $providerConfiguration['owner']);

					if (isset($providerConfiguration['remote'])) {
						$provider->setRemoteName($providerConfiguration['remote']);
					}
					if (isset($providerConfiguration['repositories'])) {
						$provider->setRepositories($providerConfiguration['repositories']);
					}
					if (isset($providerConfiguration['tag'])) {
						if (isset($providerConfiguration['tag']['sorting'])) {
							$provider->setTagSorting($providerConfiguration['tag']['sorting']);
						}
						if (isset($providerConfiguration['tag']['compareFunction'])) {
							$provider->setTagCompareFunction($providerConfiguration['tag']['compareFunction']);
						}
						if (isset($providerConfiguration['tag']['limit'])) {
							$provider->setTagLimit($providerConfiguration['tag']['limit']);
						}
					}
					if (isset($providerConfiguration['auth'])) {
						if (!isset($providerConfiguration['auth']['type'])) {
							throw new IncompleteConfigurationException(sprintf(
								'Mandatory configuration field "providers[%s].auth.type" missing',
								$providerName
							));
						}

						switch ($providerConfiguration['auth']['type']) {
							case 'basic':
								if (!isset($providerConfiguration['auth']['username'])) {
									throw new IncompleteConfigurationException(sprintf(
										'Mandatory configuration field "providers[%s].auth.username" missing',
										$providerName
									));
								}
								if (!isset($providerConfiguration['auth']['password'])) {
									throw new IncompleteConfigurationException(sprintf(
										'Mandatory configuration field "providers[%s].auth.password" missing',
										$providerName
									));
								}
								$auth = new BasicAuth(
									$providerConfiguration['auth']['username'],
									$providerConfiguration['auth']['password']
								);
								$provider->setAuthentication($auth);
								break;

							default:
								throw new InvalidConfigurationException(sprintf(
									'Authentication type "%s" is not supported by bitbucket',
									$providerConfiguration['type']
								));
						}
					}
					if (isset($providerConfiguration['blacklist'])) {
						$provider->setBlacklist((array) $providerConfiguration['blacklist']);
					}

					$compoundProvider->addProvider($provider);
					break;

				case 'github':
					if (!isset($providerConfiguration['owner'])) {
						throw new IncompleteConfigurationException(sprintf(
							'Mandatory configuration field "providers[%s].owner" missing',
							$providerName
						));
					}

					$provider = new GithubProvider($environment, $providerName, $providerConfiguration['owner']);

					if (isset($providerConfiguration['remote'])) {
						$provider->setRemoteName($providerConfiguration['remote']);
					}
					if (isset($providerConfiguration['repositories'])) {
						$provider->setRepositories($providerConfiguration['repositories']);
					}
					if (isset($providerConfiguration['tag'])) {
						if (isset($providerConfiguration['tag']['sorting'])) {
							$provider->setTagSorting($providerConfiguration['tag']['sorting']);
						}
						if (isset($providerConfiguration['tag']['compareFunction'])) {
							$provider->setTagCompareFunction($providerConfiguration['tag']['compareFunction']);
						}
						if (isset($providerConfiguration['tag']['limit'])) {
							$provider->setTagLimit($providerConfiguration['tag']['limit']);
						}
					}
					if (isset($providerConfiguration['auth'])) {
						if (!isset($providerConfiguration['auth']['type'])) {
							throw new IncompleteConfigurationException(sprintf(
								'Mandatory configuration field "providers[%s].auth.type" missing',
								$providerName
							));
						}

						switch ($providerConfiguration['auth']['type']) {
							case 'basic':
								if (!isset($providerConfiguration['auth']['username'])) {
									throw new IncompleteConfigurationException(sprintf(
										'Mandatory configuration field "providers[%s].auth.username" missing',
										$providerName
									));
								}
								if (!isset($providerConfiguration['auth']['password'])) {
									throw new IncompleteConfigurationException(sprintf(
										'Mandatory configuration field "providers[%s].auth.password" missing',
										$providerName
									));
								}
								$auth = new BasicAuth(
									$providerConfiguration['auth']['username'],
									$providerConfiguration['auth']['password']
								);
								$provider->setAuthentication($auth);
								break;

							case 'accessToken':
								if (!isset($providerConfiguration['auth']['accessToken'])) {
									throw new IncompleteConfigurationException(sprintf(
										'Mandatory configuration field "providers[%s].auth.accessToken" missing',
										$providerName
									));
								}
								$auth = new AccessTokenAuth(
									$providerConfiguration['auth']['accessToken']
								);
								$provider->setAuthentication($auth);
								break;

							default:
								throw new InvalidConfigurationException(sprintf(
									'Authentication type "%s" is not supported by github',
									$providerConfiguration['type']
								));
						}
					}
					if (isset($providerConfiguration['blacklist'])) {
						$provider->setBlacklist((array) $providerConfiguration['blacklist']);
					}

					$compoundProvider->addProvider($provider);
					break;

				default:
					throw new InvalidConfigurationException(sprintf(
						'Provider type "%s" is not supported',
						$providerConfiguration['type']
					));
			}
		}

		if (isset($array['pre'])) {
			$compoundAction = new CompoundAction();
			$configuration->setPreAction($compoundAction);

			foreach ($array['pre'] as $action) {
				$action = (array) $action;
				$action = $this->parseAction($action);

				if ($action) {
					$compoundAction->addAction($action);
				}
			}
		}

		if (isset($array['actions'])) {
			$compoundAction = new CompoundAction();
			$configuration->setAction($compoundAction);

			foreach ($array['actions'] as $action) {
				$action = (array) $action;
				$action = $this->parseAction($action);

				if ($action) {
					$compoundAction->addAction($action);
				}
			}
		}

		if (isset($array['post'])) {
			$compoundAction = new CompoundAction();
			$configuration->setPostAction($compoundAction);

			foreach ($array['post'] as $action) {
				$action = (array) $action;
				$action = $this->parseAction($action);

				if ($action) {
					$compoundAction->addAction($action);
				}
			}
		}

		if (isset($array['variables'])) {
			$configuration->setVariables($array['variables']);
		}

		return $configuration;
	}

	protected function parseAction($actionConfiguration, array $inheritSettings = array())
	{
		$action = false;

		if (isset($actionConfiguration['exec'])) {
			$arguments = (array) $actionConfiguration['exec'];
			$command   = array_shift($arguments);
			$settings  = array_merge($inheritSettings, $actionConfiguration);

			$action = new ProcessAction($command, $arguments, $settings);
		}
		else if (isset($actionConfiguration['command'])) {
			$arguments = (array) $actionConfiguration['command'];
			$command   = array_shift($arguments);
			$settings  = array_merge($inheritSettings, $actionConfiguration);

			$action = new CommandAction($command, $arguments, $settings);
		}
		else if (isset($actionConfiguration['json'])) {
			$file = $actionConfiguration['json'];
			$settings  = array_merge($inheritSettings, $actionConfiguration);

			$action = new JsonAction($file, $settings);
		}
		else if (isset($actionConfiguration['actions'])) {
			$compoundAction = new CompoundAction();

			foreach ($actionConfiguration['actions'] as $childAction) {
				$compoundAction->addAction($this->parseAction($childAction, $actionConfiguration));
			}

			$action = $compoundAction;
		}
		else if (!empty($actionConfiguration)) {
			$settings = $actionConfiguration[count($actionConfiguration)-1];
			if (is_array($settings)) {
				array_pop($actionConfiguration);
				$settings = array_merge($inheritSettings, $settings);
			}
			else {
				$settings = $inheritSettings;
			}
			while (is_array($actionConfiguration[0])) {
				$actionConfiguration = array_shift($actionConfiguration);
			}
			$command   = array_shift($actionConfiguration);
			$arguments = $actionConfiguration;

			$action = new ProcessAction($command, $arguments, $settings);
		}

		if ($action && isset($actionConfiguration['if'])) {
			$condition = new AndCondition();
			$this->parseCondition($actionConfiguration['if'], $condition);
			$action->setCondition($condition);
		}

		return $action;
	}

	protected function parseCondition(array $conditionConfiguration, ConjunctionCondition $parentCondition)
	{
		$keys = array_keys($conditionConfiguration);
		$keys = array_filter($keys, 'is_numeric');

		// regular array
		if (count($keys)) {
			foreach ($conditionConfiguration as $item) {
				$this->parseCondition($item, $parentCondition);
			}
		}
		// associative array
		else {
			foreach ($conditionConfiguration as $type => $settings) {
				switch ($type) {
					case 'and':
						if ($parentCondition instanceof AndCondition) {
							$condition = $parentCondition;
						}
						else {
							$condition = new AndCondition();
							$parentCondition->addCondition($condition);
						}
						$this->parseCondition($settings, $condition);
						break;

					case 'or':
						if ($parentCondition instanceof OrCondition) {
							$condition = $parentCondition;
						}
						else {
							$condition = new OrCondition();
							$parentCondition->addCondition($condition);
						}
						$this->parseCondition($settings, $condition);
						break;

					case 'not':
						$condition = new AndCondition();
						$this->parseCondition($settings, $condition);
						$condition = new NotCondition($condition);
						$parentCondition->addCondition($condition);
						break;

					case 'fileExists':
						$condition = new FileExistsCondition($settings);
						$parentCondition->addCondition($condition);
						break;

					case 'provider':
						$condition = new ProviderCondition($settings);
						$parentCondition->addCondition($condition);
						break;

					default:
						throw new InvalidConfigurationException(
							sprintf('Condition %s is not supported', $type)
						);
				}
			}
		}
	}
}
