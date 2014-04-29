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

namespace ContaoCommunityAlliance\BuildSystem\Repositories\Action;

use ContaoCommunityAlliance\BuildSystem\Repositories\Environment;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\Repository;
use ContaoCommunityAlliance\BuildSystem\Repository\GitRepository;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class CompoundAction extends AbstractAction
{
	/**
	 * @var ActionInterface[]
	 */
	protected $actions = array();

	function __construct(array $actions = array())
	{
		$this->addActions($actions);
	}

	public function clearActions()
	{
		$this->actions = array();
		return $this;
	}

	public function addAction(ActionInterface $action)
	{
		$this->actions[] = $action;
		return $this;
	}

	public function addActions(array $actions)
	{
		foreach ($actions as $action) {
			$this->addAction($action);
		}
		return $this;
	}

	/**
	 * @param ActionInterface[] $actions
	 */
	public function setActions($actions)
	{
		$this->clearActions();
		$this->addActions($actions);
		return $this;
	}

	/**
	 * @return ActionInterface[]
	 */
	public function getActions()
	{
		return $this->actions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(Environment $environment)
	{
		if ($this->condition && !$this->condition->accept($this, $environment)) {
			return;
		}

		foreach ($this->actions as $action) {
			$action->run($environment);
		}
	}
}
