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

use Traversable;


/**
 * An abstract conjunction condition.
 */
abstract class ConjunctionCondition implements ConditionInterface, \Countable, \IteratorAggregate
{
	/**
	 * The conjunct conditions.
	 *
	 * @var ConditionInterface[]
	 */
	protected $conditions = array();

	public function clearConditions()
	{
		$this->conditions = array();
	}

	public function addCondition(ConditionInterface $condition)
	{
		$this->conditions[] = $condition;
	}

	public function addConditions($conditions)
	{
		foreach ($conditions as $condition) {
			$this->addCondition($condition);
		}
	}

	public function setConditions($conditions)
	{
		$this->clearConditions();
		$this->addConditions($conditions);
	}

	public function getConditions()
	{
		return $this->conditions;
	}

	/**
	 * {@inheritdoc}
	 */
	public function count()
	{
		return count($this->conditions);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->conditions);
	}
}
