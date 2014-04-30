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
 * Provider condition.
 */
class ProviderCondition implements ConditionInterface
{
	/**
	 * @var string
	 */
	protected $name;

	public function __construct($name)
	{
		$this->name = (string) $name;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = (String) $name;
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
	 * {@inheritdoc}
	 */
	public function accept(ActionInterface $action, Environment $environment)
	{
		$name = $environment->getPlaceholderReplacer()->replace($this->name, $environment);

		return $environment->getRepository()->getProvider()->getName() == $name;
	}
}
