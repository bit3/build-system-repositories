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
 * Repository information.
 */
class Repository
{
	/**
	 * Git repository type.
	 */
	const TYPE_GIT = 'git';

	/**
	 * Subversion repository type.
	 */
	const TYPE_SUBVERSION = 'svn';

	/**
	 * Mercurial repository type.
	 */
	const TYPE_MERCURIAL = 'hg';

	const REF_BRANCH = 'branch';

	const REF_TAG = 'tag';

	/**
	 * The remote name.
	 *
	 * @var string
	 */
	protected $remoteName;

	/**
	 * The repository owner name.
	 *
	 * @var string
	 */
	protected $owner;

	/**
	 * The repository name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The repository type, e.g. Repository::TYPE_GIT.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The repository url.
	 *
	 * @var string
	 */
	protected $readUrl;

	/**
	 * The write url.
	 *
	 * @var string
	 */
	protected $writeUrl;

	/**
	 * A web url, e.g. the github repository url (not the git url).
	 *
	 * @var string|null
	 */
	protected $webUrl;

	/**
	 * The working ref name, e.g. "master" or "1.2.3".
	 *
	 * @var string
	 */
	protected $ref;

	/**
	 * The real ref name, e.g. "origin/master" or "1.2.3".
	 *
	 * @var string
	 */
	protected $realRef;

	/**
	 * The ref type, e.g. "branch" or "tag".
	 *
	 * @var string
	 */
	protected $refType;

	/**
	 * Determine if the repository is readonly,
	 * e.g. if the repository can only fetched through $url, but not pushed.
	 *
	 * @var bool
	 */
	protected $readonly = false;

	function __construct($owner, $name, $type)
	{
		$this->setOwner($owner);
		$this->setName($name);
		$this->setType($type);
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
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = (string) $name;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $type
	 */
	public function setType($type)
	{
		if (!in_array($type, array(static::TYPE_GIT, static::TYPE_SUBVERSION, static::TYPE_MERCURIAL))) {
			throw new \InvalidArgumentException(sprintf('"%s" is not a valid repository type', $type));
		}

		$this->type = (string) $type;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param string $url
	 */
	public function setReadUrl($url)
	{
		$this->readUrl = (string) $url;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getReadUrl()
	{
		return $this->readUrl;
	}

	/**
	 * @param string $writeUrl
	 */
	public function setWriteUrl($writeUrl)
	{
		$this->writeUrl = (string) $writeUrl;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getWriteUrl()
	{
		return $this->writeUrl;
	}

	/**
	 * @param string $webUrl
	 */
	public function setWebUrl($webUrl)
	{
		$this->webUrl = empty($webUrl) ? null : (string) $webUrl;
		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getWebUrl()
	{
		return $this->webUrl;
	}

	/**
	 * @param string $branch
	 */
	public function setRef($branch)
	{
		$this->ref = (string) $branch;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRef()
	{
		return $this->ref;
	}

	/**
	 * @param string $realRef
	 */
	public function setRealRef($realRef)
	{
		$this->realRef = (string) $realRef;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRealRef()
	{
		return $this->realRef;
	}

	/**
	 * @param string $refType
	 */
	public function setRefType($refType)
	{
		$this->refType = (string) $refType;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getRefType()
	{
		return $this->refType;
	}

	/**
	 * @param boolean $readonly
	 */
	public function setReadonly($readonly)
	{
		$this->readonly = (bool) $readonly;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public function isReadonly()
	{
		return $this->readonly;
	}
}
