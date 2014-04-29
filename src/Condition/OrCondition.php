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
use Traversable;


/**
 * OR conjunction condition.
 */
class OrCondition extends ConjunctionCondition
{
	/**
	 * {@inheritdoc}
	 */
	public function accept(ActionInterface $action, Environment $environment)
	{
		if (empty($this->conditions)) {
			return true;
		}

		foreach ($this->conditions as $condition) {
			if ($condition->accept($action, $environment)) {
				return true;
			}
		}

		return false;
	}
}
