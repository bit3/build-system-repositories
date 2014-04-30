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
class BitBucketProvider implements ProviderInterface
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
	protected $remoteName = 'bitbucket';

	/**
	 * The name of the owner on bitbucket.
	 *
	 * @var string
	 */
	protected $owner;

	/**
	 * A regular expression to filter repositories.
	 *
	 * @var string
	 */
	protected $repositoryFilter = '.*';

	/**
	 * Sorted list of branches to select.
	 *
	 * @var array
	 */
	protected $branches = array('master', 'develop');

	/**
	 * List of tags to select.
	 *
	 * @var array
	 */
	protected $tags = array();

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
	 * @var AuthInterface
	 */
	protected $authentication;

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
	 * @param string $repositoryFilter
	 */
	public function setRepositoryFilter($repositoryFilter)
	{
		$this->repositoryFilter = '~' . str_replace(
				'~',
				'\\~',
				empty($repositoryFilter) ? '.*' : (string) $repositoryFilter
			) . '~';
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRepositoryFilter()
	{
		return $this->repositoryFilter;
	}

	/**
	 * @param array $branches
	 */
	public function setBranches(array $branches)
	{
		$branches       = array_map(
			'strval',
			$branches
		);
		$this->branches = $branches;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getBranches()
	{
		return $this->branches;
	}

	/**
	 * @param array $tags
	 */
	public function setTags(array $tags)
	{
		$tags       = array_map(
			'strval',
			$tags
		);
		$this->tags = $tags;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getTags()
	{
		return $this->tags;
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
	 * @return \Guzzle\Http\Client
	 */
	protected function getClient()
	{
		if ($this->client === null) {
			$this->client = new Client('https://bitbucket.org/api/1.0/');

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

					case AuthInterface::OAUTH:
						// TODO
						throw new \RuntimeException('Incomplete implementation');
						break;

					default:
						throw new \RuntimeException(sprintf(
							'Authentication via "%s" is not supported by bitbucket',
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
				sprintf('[ccabs:repositories:bit-bucket-provider] list all repositories for %s', $this->owner)
			);
		}
		if (
			$this->environment->hasOutput() &&
			$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
		) {
			$this->environment->getOutput()->writeln(
				sprintf(' * <info>ccabs:repositories:bit-bucket-provider</info> list all repositories for <comment>%s</comment>', $this->owner)
			);
		}

		$url          = sprintf('users/%s', rawurlencode($this->owner));
		$request      = $this->getClient()->get($url);
		$response     = $request->send();
		$data         = $response->json();
		$repositories = array();

		foreach ($data['repositories'] as $repositoryData) {
			if (!preg_match($this->repositoryFilter, $repositoryData['name'])) {
				continue;
			}

			$this->buildRepositories($repositoryData, $repositories);
		}

		if ($this->environment->hasLogger()) {
			$this->environment->getLogger()->debug(
				sprintf(
					'[ccabs:repositories:bit-bucket-provider] found %d repositories',
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
					' * <info>ccabs:repositories:bit-bucket-provider</info> found %d repositories',
					count($repositories)
				)
			);
		}

		return $repositories;
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
				sprintf('[ccabs:repositories:bit-bucket-provider] get repository for %s/%s', $owner, $name)
			);
		}
		if (
			$this->environment->hasOutput() &&
			$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
		) {
			$this->environment->getOutput()->writeln(
				sprintf(' * <info>ccabs:repositories:bit-bucket-provider</info> get repository for <comment>%s/%s</comment>', $owner, $name)
			);
		}

		$url            = sprintf('repositories/%s/%s', rawurlencode($owner), rawurlencode($name));
		$request        = $this->getClient()->get($url);
		$response       = $request->send();
		$repositoryData = $response->json();

		$repositories = $this->buildRepositories($repositoryData);

		if ($this->environment->hasLogger()) {
			$this->environment->getLogger()->debug(
				sprintf(
					'[ccabs:repositories:bit-bucket-provider] found %d refs: %s',
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
					' * <info>ccabs:repositories:bit-bucket-provider</info> found %d refs: %s',
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

	protected function buildRepositories($repositoryData, &$repositories = array())
	{
			if ($this->environment->hasLogger()) {
				$this->environment->getLogger()->debug(
					sprintf(
						'[ccabs:repositories:bit-bucket-provider] build repository versions for %s/%s',
						$repositoryData['owner'],
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
						' * <info>ccabs:repositories:bit-bucket-provider</info> build repository versions for <comment>%s/%s</comment>',
						$repositoryData['owner'],
						$repositoryData['name']
					)
				);
			}

		/** @var TagMatcher[] $branchMatchers */
		$branchMatchers = array();
		foreach ($this->branches as $branchSyntax) {
			$branchMatchers[] = new TagMatcher($branchSyntax);
		}
		/** @var TagMatcher[] $tagMatchers */
		$tagMatchers = array();
		foreach ($this->tags as $tagSyntax) {
			$tagMatchers[] = new TagMatcher($tagSyntax);
		}

		if (count($branchMatchers)) {
			$url          = sprintf(
				'repositories/%s/%s/branches',
				rawurlencode($repositoryData['owner']),
				rawurlencode($repositoryData['name'])
			);
			$request      = $this->getClient()->get($url);
			$response     = $request->send();
			$branchesData = $response->json();

			if ($this->environment->hasLogger()) {
				$this->environment->getLogger()->debug(
					sprintf(
						'[ccabs:repositories:bit-bucket-provider] found %d branches: %s',
						count($branchesData),
						implode(', ', array_keys($branchesData))
					)
				);
			}
			if (
				$this->environment->hasOutput() &&
				$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
			) {
				$this->environment->getOutput()->writeln(
					sprintf(
						' * <info>ccabs:repositories:bit-bucket-provider</info> found %d branches: %s',
						count($branchesData),
						implode(', ', array_keys($branchesData))
					)
				);
			}

			foreach ($branchMatchers as $branchMatcher) {
				foreach ($branchesData as $branchName => $branchData) {
					if ($branchMatcher->match($branchName)) {
						$repository = $this->buildRepository($repositoryData, $branchName, $this->remoteName . '/' . $branchName, Repository::REF_BRANCH);

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

			$url      = sprintf(
				'repositories/%s/%s/tags',
				rawurlencode($repositoryData['owner']),
				rawurlencode($repositoryData['name'])
			);
			$request  = $this->getClient()->get($url);
			$response = $request->send();
			$tagsData = $response->json();

			if ($this->environment->hasLogger()) {
				$this->environment->getLogger()->debug(
					sprintf(
						'[ccabs:repositories:bit-bucket-provider] found %d tags: %s',
						count($tagsData),
						implode(', ', array_keys($tagsData))
					)
				);
			}
			if (
				$this->environment->hasOutput() &&
				$this->environment->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_DEBUG
			) {
				$this->environment->getOutput()->writeln(
					sprintf(
						' * <info>ccabs:repositories:bit-bucket-provider</info> found %d tags: %s',
						count($tagsData),
						implode(', ', array_keys($tagsData))
					)
				);
			}

			foreach ($tagMatchers as $tagMatcher) {
				foreach ($tagsData as $tagName => $tagData) {
					if ($tagMatcher->match($tagName)) {
						$repository = $this->buildRepository($repositoryData, $tagName, $tagName, Repository::REF_TAG);

						if ($repository) {
							$tags[$tagName] = $repository;
						}
					}
				}
			}

			// sort tags
			if ($this->tagCompareFunction) {
				uksort($tags, $this->tagCompareFunction);
			}

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

		return $repositories;
	}

	protected function buildRepository($repositoryData, $ref, $realRef, $refType)
	{
		$repository = new Repository(
			$this,
			$repositoryData['owner'],
			$repositoryData['slug'],
			$repositoryData['scm']
		);
		$repository->setRemoteName($this->remoteName);
		$repository->setReadUrl(
			sprintf('https://bitbucket.org/%s/%s.git', $repositoryData['owner'], $repositoryData['slug'])
		);
		$repository->setWriteUrl(
			sprintf('git@bitbucket.org:%s/%s.git', $repositoryData['owner'], $repositoryData['slug'])
		);
		$repository->setWebUrl(
			sprintf('https://bitbucket.org/%s/%s', $repositoryData['owner'], $repositoryData['slug'])
		);
		$repository->setRef($ref);
		$repository->setRealRef($realRef);
		$repository->setRefType($refType);
		$repository->setReadonly($repositoryData['read_only']);
		return $repository;
	}
}
