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

use ContaoCommunityAlliance\BuildSystem\Repositories\Condition\ConditionInterface;
use ContaoCommunityAlliance\BuildSystem\Repositories\Environment;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\Repository;
use ContaoCommunityAlliance\BuildSystem\Repository\GitRepository;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractAction implements ActionInterface
{
	/**
	 * @var ConditionInterface
	 */
	protected $condition;

	/**
	 * {@inheritdoc}
	 */
	public function setCondition(ConditionInterface $condition = null)
	{
		$this->condition = $condition;
	}
}
