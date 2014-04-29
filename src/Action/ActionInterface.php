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

interface ActionInterface
{
	/**
	 * Run the action on a repository.
	 *
	 * @param Environment $environment
	 */
	public function run(Environment $environment);

	/**
	 * Set the condition for this action.
	 *
	 * @param ConditionInterface $condition
	 */
	public function setCondition(ConditionInterface $condition = null);
}
