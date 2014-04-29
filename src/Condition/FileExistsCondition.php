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
 * File exists condition.
 */
class FileExistsCondition implements ConditionInterface
{
	/**
	 * @var string
	 */
	protected $file;

	public function __construct($file)
	{
		$this->file = (string) $file;
	}

	/**
	 * @param string $file
	 */
	public function setFile($file)
	{
		$this->file = (String) $file;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * {@inheritdoc}
	 */
	public function accept(ActionInterface $action, Environment $environment)
	{
		$file = $environment->getPlaceholderReplacer()->replace($this->file, $environment);

		return file_exists($file);
	}
}
