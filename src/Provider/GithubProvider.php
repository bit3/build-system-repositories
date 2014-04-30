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
use ContaoCommunityAlliance\BuildSystem\Repositories\Environment;
use ContaoCommunityAlliance\BuildSystem\Repositories\Util\TagMatcher;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Exception\ClientErrorResponseException;
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
class GithubProvider implements ProviderInterface
{
	/**
	 * @var Environment
	 */
	protected $environment;

	/**
	 * The provider name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $remoteName = 'github';

	/**
	 * The name of the owner on github.
	 *
	 * @var string
	 */
	protected $owner;

	/**
	 * Repository specific settings.
	 *
	 * @var array
	 */
	protected $repositories = array();

	/**
	 * Tag sorting.
	 *
	 * @var string
	 */
	protected $tagSorting = 'desc';

	/**
	 * Tag compare function.
	 *
	 * @var string
	 */
	protected $tagCompareFunction = 'version_compare';

	/**
	 * Max tag count.
	 *
	 * @var int
	 */
	protected $tagLimit = -1;

	/**
	 * The authentication information.
	 *
	 * @var AuthInterface|BasicAuth|AccessTokenAuth
	 */
	protected $authentication;

	/**
	 * Blacklisted repositories.
	 *
	 * @var array
	 */
	protected $blacklist = array();

	/**
	 * The HTTP client.
	 *
	 * @var Client
	 */
	protected $client;

	public function __construct(
		Environment $environment,
		$name,
		$owner
	) {
		$this->environment = $environment;
		$this->name        = $name;
		$this->setOwner($owner);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $remoteName
	 */
	public function setRemoteName($remoteName)
	{
		$this->remoteName = (string) $remoteName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRemoteName()
	{
		return $this->remoteName;
	}

	/**
	 * @param string $owner
	 */
	public function setOwner($owner)
	{
		$this->owner = (string) $owner;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getOwner()
	{
		return $this->owner;
	}

	/**
	 * @param array $repositories
	 */
	public function setRepositories($repositories)
	{
		$this->repositories = $repositories;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getRepositories()
	{
		return $this->repositories;
	}

	/**
	 * @param string $tagSorting
	 */
	public function setTagSorting($tagSorting)
	{
		$this->tagSorting = strtolower($tagSorting);
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTagSorting()
	{
		return $this->tagSorting;
	}

	/**
	 * @param string $tagCompareFunction
	 */
	public function setTagCompareFunction($tagCompareFunction)
	{
		$this->tagCompareFunction = empty($tagCompareFunction) ? false : (string) $tagCompareFunction;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getTagCompareFunction()
	{
		return $this->tagCompareFunction;
	}

	/**
	 * @param int $tagLimit
	 */
	public function setTagLimit($tagLimit)
	{
		$this->tagLimit = (int) $tagLimit;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getTagLimit()
	{
		return $this->tagLimit;
	}

	/**
	 * Set the authentication information.
	 *
	 * @param AuthInterface $authentication
	 */
	public function setAuthentication(AuthInterface $authentication = null)
	{
		if ($this->authentication !== $authentication) {
			$this->client = null;
		}

		$this->authentication = $authentication;
		return $this;
	}

	/**
	 * Return the authentication information.
	 *
	 * @return AuthInterface
	 */
	public function getAuthentication()
	{
		return $this->authentication;
	}

	/**
	 * @param array $blacklist
	 */
	public function setBlacklist(array $blacklist)
	{
		$this->blacklist = $blacklist;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getBlacklist()
	{
		return $this->blacklist;
	}

	/**
	 * @return \Guzzle\Http\Client
	 */
	protected function getClient()
	{
		if ($this->client === null) {
			$this->client = new Client('https://api.github.com/');
			$this->client->setDefaultOption('headers/Accept', 'application/vnd.github.v3+json');

			if ($this->authentication) {
				switch ($this->authentication->getType()) {
					case AuthInterface::BASIC:
						$this->client->getConfig()->setPath(
							'request.options/auth',
							array(
								$this->authentication->getUsername(),
								$this->authentication->getPassword(),
								'Basic|Digest|NTLM|Any',
							)
						);
						break;

					case AuthInterface::ACCESS_TOKEN:
						$this->client->setDefaultOption(
							'headers/Authorization',
							'token ' . $this->authentication->getAccessToken()
						);
						break;

					default:
						throw new \RuntimeException(sprintf(
							'Authentication via "%s" is not supported by github',
							$this->authentication->getType()
						));
				}
			}
		}

		return $this->client;
	}

	/**
	 * {@inheritdoc}
	 */
	public function listAll()
	{
		if ($this->environment->hasLogger()) {
			$this->environment->getLogger()->debug(
				sprintf('[ccabs:repositories:github-provider] list all repositories for %s', $this->owner)
			);
		}
		if (
			$this->environment->hasOutput() &&
			$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
		) {
			$this->environment->getOutput()->writeln(
				sprintf(
					' * <info>ccabs:repositories:github-provider</info> list all repositories for <comment>%s</comment>',
					$this->owner
				)
			);
		}

		try {
			$repositories = array();

			for ($page = 1; true; $page++) {
				$url      = sprintf('users/%s/repos?page=%d', rawurlencode($this->owner), $page);
				$request  = $this->getClient()->get($url);
				$response = $request->send();
				$data     = $response->json();

				foreach ($data as $repositoryData) {
					$this->buildRepositories($repositoryData, $repositories);
				}

				if (!count($data)) {
					break;
				}
			}

			if ($this->environment->hasLogger()) {
				$this->environment->getLogger()->debug(
					sprintf(
						'[ccabs:repositories:github-provider] found %d repositories',
						count($repositories)
					)
				);
			}
			if (
				$this->environment->hasOutput() &&
				$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
			) {
				$this->environment->getOutput()->writeln(
					sprintf(
						' * <info>ccabs:repositories:github-provider</info> found %d repositories',
						count($repositories)
					)
				);
			}

			return $repositories;
		}
		catch (BadResponseException $e) {
			if ($this->environment->hasLogger()) {
				$this->environment->getLogger()->debug(
					sprintf(
						'[ccabs:repositories:github-provider] bad response from github: %s',
						$e->getMessage()
					)
				);
			}
			if (
				$this->environment->hasOutput() &&
				$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
			) {
				$this->environment->getOutput()->writeln(
					sprintf(
						' * <info>ccabs:repositories:github-provider</info> bad response from github: %s',
						$e->getMessage()
					)
				);
			}

			return array();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($owner, $name)
	{
		if ($this->owner != $owner) {
			return array();
		}

		if ($this->environment->hasLogger()) {
			$this->environment->getLogger()->debug(
				sprintf('[ccabs:repositories:github-provider] get repository for %s/%s', $owner, $name)
			);
		}
		if (
			$this->environment->hasOutput() &&
			$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
		) {
			$this->environment->getOutput()->writeln(
				sprintf(
					' * <info>ccabs:repositories:github-provider</info> get repository for <comment>%s/%s</comment>',
					$owner,
					$name
				)
			);
		}

		try {
			$url            = sprintf('repos/%s/%s', rawurlencode($owner), rawurlencode($name));
			$request        = $this->getClient()->get($url);
			$response       = $request->send();
			$repositoryData = $response->json();

			$repositories = $this->buildRepositories($repositoryData);

			if ($this->environment->hasLogger()) {
				$this->environment->getLogger()->debug(
					sprintf(
						'[ccabs:repositories:github-provider] found %d refs: %s',
						count($repositories),
						implode(
							', ',
							array_map(
								function (Repository $repository) {
									return $repository->getRef();
								},
								$repositories
							)
						)
					)
				);
			}
			if (
				$this->environment->hasOutput() &&
				$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
			) {
				$this->environment->getOutput()->writeln(
					sprintf(
						' * <info>ccabs:repositories:github-provider</info> found %d refs: %s',
						count($repositories),
						implode(
							', ',
							array_map(
								function (Repository $repository) {
									return $repository->getRef();
								},
								$repositories
							)
						)
					)
				);
			}

			return $repositories;
		}
		catch (BadResponseException $e) {
			if ($this->environment->hasLogger()) {
				$this->environment->getLogger()->debug(
					sprintf(
						'[ccabs:repositories:github-provider] bad response from %s/%s repository: %s',
						$owner,
						$name,
						$e->getMessage()
					)
				);
			}
			if (
				$this->environment->hasOutput() &&
				$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
			) {
				$this->environment->getOutput()->writeln(
					sprintf(
						' * <info>ccabs:repositories:github-provider</info> bad response from <comment>%s/%s</comment> repository: %s',
						$owner,
						$name,
						$e->getMessage()
					)
				);
			}

			return array();
		}
	}

	protected function buildRepositories($repositoryData, &$repositories = array())
	{
		if (in_array($repositoryData['name'], $this->blacklist)) {
			return array();
		}

		$settings = array();

		foreach ($this->repositories as $pattern => $repositorySettings) {
			$matcher = new TagMatcher($pattern);
			if ($matcher->match($repositoryData['name'])) {
				$settings = array_merge($settings, $repositorySettings);
			}
		}

		if (empty($settings)) {
			return array();
		}

		if ($this->environment->hasLogger()) {
			$this->environment->getLogger()->debug(
				sprintf(
					'[ccabs:repositories:github-provider] build repository versions for %s/%s',
					$repositoryData['owner']['login'],
					$repositoryData['name']
				)
			);
		}
		if (
			$this->environment->hasOutput() &&
			$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
		) {
			$this->environment->getOutput()->writeln(
				sprintf(
					' * <info>ccabs:repositories:github-provider</info> build repository versions for <comment>%s/%s</comment>',
					$repositoryData['owner']['login'],
					$repositoryData['name']
				)
			);
		}

		/** @var TagMatcher[] $branchMatchers */
		$branchMatchers = array();
		if (isset($settings['branches'])) {
			foreach ($settings['branches'] as $branchSyntax) {
				$branchMatchers[] = new TagMatcher($branchSyntax);
			}
		}
		/** @var TagMatcher[] $tagMatchers */
		$tagMatchers = array();
		if (isset($settings['tags'])) {
			foreach ($settings['tags'] as $tagSyntax) {
				$tagMatchers[] = new TagMatcher($tagSyntax);
			}
		}

		if (count($branchMatchers) || count($tagMatchers)) {
			try {
				$url      = sprintf(
					'repos/%s/%s/git/refs',
					rawurlencode($repositoryData['owner']['login']),
					rawurlencode($repositoryData['name'])
				);
				$request  = $this->getClient()->get($url);
				$response = $request->send();
				$refsData = $response->json();

				$branchNames = array();
				$tagNames    = array();

				foreach ($refsData as $refData) {
					if (preg_match('~^refs/(heads|tags)/(.*)$~', $refData['ref'], $matches)) {
						switch ($matches[1]) {
							case 'heads':
								$branchNames[] = $matches[2];
								break;
							case 'tags':
								$tagNames[] = $matches[2];
								break;
						}
					}
				}

				if (count($branchMatchers)) {
					if ($this->environment->hasLogger()) {
						$this->environment->getLogger()->debug(
							sprintf(
								'[ccabs:repositories:github-provider] found %d branches: %s',
								count($branchNames),
								implode(', ', $branchNames)
							)
						);
					}
					if (
						$this->environment->hasOutput() &&
						$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
					) {
						$this->environment->getOutput()->writeln(
							sprintf(
								' * <info>ccabs:repositories:github-provider</info> found %d branches: %s',
								count($branchNames),
								implode(', ', $branchNames)
							)
						);
					}

					foreach ($branchMatchers as $branchMatcher) {
						foreach ($branchNames as $branchName) {
							if ($branchMatcher->match($branchName)) {
								$repository = $this->buildRepository(
									$repositoryData,
									$branchName,
									$this->remoteName . '/' . $branchName,
									Repository::REF_BRANCH
								);

								if ($repository) {
									$repositories[] = $repository;
								}
							}
						}
					}
				}

				if (count($tagMatchers)) {
					// tags cannot be directly added to $repositories, store them in a temporary array
					$tags = array();

					if ($this->environment->hasLogger()) {
						$this->environment->getLogger()->debug(
							sprintf(
								'[ccabs:repositories:github-provider] found %d tags: %s',
								count($tagNames),
								implode(', ', $tagNames)
							)
						);
					}
					if (
						$this->environment->hasOutput() &&
						$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
					) {
						$this->environment->getOutput()->writeln(
							sprintf(
								' * <info>ccabs:repositories:github-provider</info> found %d tags: %s',
								count($tagNames),
								implode(', ', $tagNames)
							)
						);
					}

					foreach ($tagMatchers as $tagMatcher) {
						foreach ($tagNames as $tagName) {
							if ($tagMatcher->match($tagName)) {
								if (
									!empty($settings['tag']['min']) &&
									call_user_func($this->tagCompareFunction, $settings['tag']['min'], $tagName) > 0 ||
									!empty($settings['tag']['max']) &&
									call_user_func($this->tagCompareFunction, $settings['tag']['max'], $tagName) < 0 ||
									!empty($settings['tag']['ignore']) &&
									in_array($tagName, (array) $settings['tag']['ignore'])
								) {
									continue;
								}

								$repository = $this->buildRepository($repositoryData, $tagName, $tagName, Repository::REF_TAG);

								if ($repository) {
									$tags[$tagName] = $repository;
								}
							}
						}
					}

					// sort tags
					uksort($tags, $this->tagCompareFunction);

					// invert sorting
					if ($this->tagSorting == 'desc') {
						$tags = array_reverse($tags);
					}

					// limit tag count
					if ($this->tagLimit > 0) {
						$tags = array_slice($tags, 0, $this->tagLimit);
					}

					// add remaining tags
					foreach ($tags as $repository) {
						$repositories[] = $repository;
					}
				}
			}
			catch (BadResponseException $e) {
				if ($this->environment->hasLogger()) {
					$this->environment->getLogger()->debug(
						sprintf(
							'[ccabs:repositories:github-provider] bad response from %s repository: %s',
							$repositoryData['full_name'],
							$e->getMessage()
						)
					);
				}
				if (
					$this->environment->hasOutput() &&
					$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
				) {
					$this->environment->getOutput()->writeln(
						sprintf(
							' * <info>ccabs:repositories:github-provider</info> bad response from <comment>%s</comment> repository: %s',
							$repositoryData['full_name'],
							$e->getMessage()
						)
					);
				}
			}
		}

		return $repositories;
	}

	protected function buildRepository($repositoryData, $ref, $realRef, $refType)
	{
		$repository = new Repository(
			$this,
			$repositoryData['owner']['login'],
			$repositoryData['name'],
			'git'
		);
		$repository->setRemoteName($this->remoteName);
		$repository->setReadUrl(
			sprintf('https://github.com/%s.git', $repositoryData['full_name'])
		);
		$repository->setWriteUrl(
			sprintf('git@github.com:%s.git', $repositoryData['full_name'])
		);
		$repository->setWebUrl(
			sprintf('https://github.com/%s', $repositoryData['full_name'])
		);
		$repository->setRef($ref);
		$repository->setRealRef($realRef);
		$repository->setRefType($refType);
		$repository->setReadonly(
			isset($repositoryData['permissions']['push'])
				? !$repositoryData['permissions']['push']
				: true
		);
		return $repository;
	}
}
