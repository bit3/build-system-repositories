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
 * Not condition.
 */
class NotCondition implements ConditionInterface
{
	/**
	 * @var ConditionInterface
	 */
	protected $condition;

	public function __construct(ConditionInterface $file)
	{
		$this->condition = $file;
	}

	/**
	 * @param ConditionInterface $file
	 */
	public function setCondition(ConditionInterface $file)
	{
		$this->condition = $file;
		return $this;
	}

	/**
	 * @return ConditionInterface
	 */
	public function getCondition()
	{
		return $this->condition;
	}

	/**
	 * {@inheritdoc}
	 */
	public function accept(ActionInterface $action, Environment $environment)
	{
		return !$this->condition->accept($action, $environment);
	}
}
