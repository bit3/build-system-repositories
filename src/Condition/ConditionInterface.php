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

namespace ContaoCommunityAlliance\BuildSystem\Repositories\Condition;

use ContaoCommunityAlliance\BuildSystem\Repositories\Action\ActionInterface;
use ContaoCommunityAlliance\BuildSystem\Repositories\Environment;

/**
 * An action condition.
 */
interface ConditionInterface
{
	/**
	 * Determine if the action is accepted.
	 *
	 * @param ActionInterface $action
	 * @param Environment     $environment
	 *
	 * @return mixed
	 */
	public function accept(ActionInterface $action, Environment $environment);
}
