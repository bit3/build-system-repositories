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
 * VCS repository provider.
 */
class VcsProvider implements ProviderInterface
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
	protected $remoteName = 'vcs';

	/**
	 * The repository owner.
	 *
	 * @var string
	 */
	protected $repositoryOwner;

	/**
	 * The repository name.
	 *
	 * @var string
	 */
	protected $repositoryName;

	/**
	 * The vcs type.
	 *
	 * @var string
	 */
	protected $vcsType;

	/**
	 * The repository url.
	 *
	 * @var string
	 */
	protected $repositoryReadUrl;

	/**
	 * The write url.
	 *
	 * @var string
	 */
	protected $repositoryWriteUrl;

	/**
	 * A web url, e.g. the github repository url (not the git url).
	 *
	 * @var string|null
	 */
	protected $repositoryWebUrl;

	/**
	 * The working ref name, e.g. "master" or "1.2.3".
	 *
	 * @var string
	 */
	protected $repositoryRef;

	/**
	 * The real ref name, e.g. "origin/master" or "1.2.3".
	 *
	 * @var string
	 */
	protected $repositoryRealRef;

	/**
	 * The ref type, e.g. "branch" or "tag".
	 *
	 * @var string
	 */
	protected $repositoryRefType;

	/**
	 * Determine if the repository is readonly,
	 * e.g. if the repository can only fetched through $url, but not pushed.
	 *
	 * @var bool
	 */
	protected $repositoryReadonly = false;

	public function __construct(
		Environment $environment,
		$name
	) {
		$this->environment = $environment;
		$this->name        = $name;
	}

	/**
	 * @return Environment
	 */
	public function getEnvironment()
	{
		return $this->environment;
	}

	/**
	 * Get the name of the provider.
	 *
	 * @return string
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
	 * @param string $repositoryOwner
	 */
	public function setRepositoryOwner($repositoryOwner)
	{
		$this->repositoryOwner = (string) $repositoryOwner;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRepositoryOwner()
	{
		return $this->repositoryOwner;
	}

	/**
	 * @param string $repositoryName
	 */
	public function setRepositoryName($repositoryName)
	{
		$this->repositoryName = (string) $repositoryName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRepositoryName()
	{
		return $this->repositoryName;
	}

	/**
	 * @param string $vcsType
	 */
	public function setVcsType($vcsType)
	{
		$this->vcsType = (string) $vcsType;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getVcsType()
	{
		return $this->vcsType;
	}

	/**
	 * @param string $repositoryReadUrl
	 */
	public function setRepositoryReadUrl($repositoryReadUrl)
	{
		$this->repositoryReadUrl = (string) $repositoryReadUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRepositoryReadUrl()
	{
		return $this->repositoryReadUrl;
	}

	/**
	 * @param string $repositoryWriteUrl
	 */
	public function setRepositoryWriteUrl($repositoryWriteUrl)
	{
		$this->repositoryWriteUrl = (string) $repositoryWriteUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRepositoryWriteUrl()
	{
		return $this->repositoryWriteUrl;
	}

	/**
	 * @param null|string $repositoryWebUrl
	 */
	public function setRepositoryWebUrl($repositoryWebUrl)
	{
		$this->repositoryWebUrl = (string) $repositoryWebUrl;
		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getRepositoryWebUrl()
	{
		return $this->repositoryWebUrl;
	}

	/**
	 * @param string $repositoryRef
	 */
	public function setRepositoryRef($repositoryRef)
	{
		$this->repositoryRef = (string) $repositoryRef;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRepositoryRef()
	{
		return $this->repositoryRef;
	}

	/**
	 * @param string $repositoryRealRef
	 */
	public function setRepositoryRealRef($repositoryRealRef)
	{
		$this->repositoryRealRef = (string) $repositoryRealRef;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRepositoryRealRef()
	{
		return $this->repositoryRealRef;
	}

	/**
	 * @param string $repositoryRefType
	 */
	public function setRepositoryRefType($repositoryRefType)
	{
		$this->repositoryRefType = (string) $repositoryRefType;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRepositoryRefType()
	{
		return $this->repositoryRefType;
	}

	/**
	 * @param boolean $repositoryReadonly
	 */
	public function setRepositoryReadonly($repositoryReadonly)
	{
		$this->repositoryReadonly = (bool) $repositoryReadonly;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function getRepositoryReadonly()
	{
		return $this->repositoryReadonly;
	}

	/**
	 * {@inheritdoc}
	 */
	public function listAll()
	{
		return array($this->getRepository());
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($owner, $name)
	{
		if (
			$owner == $this->repositoryOwner &&
			$name  == $this->repositoryName
		) {
			return array($this->getRepository());
		}

		return array();
	}

	protected function getRepository()
	{
		$repository = new Repository($this, $this->repositoryOwner, $this->repositoryName, $this->vcsType);
		$repository->setRemoteName($this->remoteName);
		$repository->setReadUrl($this->repositoryReadUrl);
		$repository->setWriteUrl($this->repositoryWriteUrl);
		$repository->setWebUrl($this->repositoryWebUrl);
		$repository->setRef($this->repositoryRef);
		$repository->setRealRef($this->repositoryRealRef);
		$repository->setRefType($this->repositoryRefType);
		$repository->setReadonly($this->repositoryReadonly);
		return $repository;
	}
}
