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

namespace ContaoCommunityAlliance\BuildSystem\Repositories;

use ContaoCommunityAlliance\BuildSystem\Repositories\Action\ActionInterface;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\ProviderInterface;

/**
 * Repositories manager configuration.
 */
class Configuration
{
	/**
	 * The local storage path.
	 *
	 * @var string
	 */
	protected $storagePath;

	/**
	 * Scheme for directory names within the storage.
	 *
	 * @var string
	 */
	protected $directoryScheme = '%repository%';

	/**
	 * The repository provider.
	 *
	 * @var ProviderInterface
	 */
	protected $provider;

	/**
	 * The pre action.
	 *
	 * @var ActionInterface
	 */
	protected $preAction;

	/**
	 * Action to run on each repository..
	 *
	 * @var ActionInterface
	 */
	protected $action;

	/**
	 * The post action.
	 *
	 * @var ActionInterface
	 */
	protected $postAction;

	/**
	 * Custom variables that can be used as placeholders.
	 *
	 * @var array
	 */
	protected $variables;

	/**
	 * @param string $storagePath
	 */
	public function setStoragePath($storagePath)
	{
		$this->storagePath = (string) $storagePath;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getStoragePath()
	{
		return $this->storagePath;
	}

	/**
	 * @param string $directoryScheme
	 */
	public function setDirectoryScheme($directoryScheme)
	{
		$this->directoryScheme = (string) $directoryScheme;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getDirectoryScheme()
	{
		return $this->directoryScheme;
	}

	/**
	 * @param ProviderInterface $provider
	 */
	public function setProvider(ProviderInterface $provider)
	{
		$this->provider = $provider;
		return $this;
	}

	/**
	 * @return ProviderInterface
	 */
	public function getProvider()
	{
		return $this->provider;
	}

	/**
	 * @param ActionInterface $preAction
	 */
	public function setPreAction(ActionInterface $preAction = null)
	{
		$this->preAction = $preAction;
		return $this;
	}

	/**
	 * @return ActionInterface
	 */
	public function getPreAction()
	{
		return $this->preAction;
	}

	/**
	 * @param ActionInterface $action
	 */
	public function setAction(ActionInterface $action)
	{
		$this->action = $action;
		return $this;
	}

	/**
	 * @return ActionInterface
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @param ActionInterface $postAction
	 */
	public function setPostAction(ActionInterface $postAction)
	{
		$this->postAction = $postAction;
		return $this;
	}

	/**
	 * @return ActionInterface
	 */
	public function getPostAction()
	{
		return $this->postAction;
	}

	/**
	 * @param array $variables
	 */
	public function setVariables(array $variables)
	{
		$this->variables = $variables;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getVariables()
	{
		return $this->variables;
	}
}
