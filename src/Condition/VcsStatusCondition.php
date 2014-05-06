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
use ContaoCommunityAlliance\BuildSystem\Repository\GitRepository;
use Traversable;


/**
 * VCS status condition.
 */
class VcsStatusCondition implements ConditionInterface
{
	/**
	 * @var array
	 */
	protected $paths;

	public function __construct(array $paths = array())
	{
		$this->paths = $paths;
	}

	/**
	 * @param array $paths
	 */
	public function setPaths(array $paths)
	{
		$this->paths = $paths;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * {@inheritdoc}
	 */
	public function accept(ActionInterface $action, Environment $environment)
	{
		if ($environment->getVcs() instanceof GitRepository) {
			$git = $environment->getVcs();

			$pathspecs = array_keys($this->paths);
			$status    = $git->status()->getWorkTreeStatus();

			foreach ($status as $modifiedPathspec => $modifiedStatus) {
				foreach ($pathspecs as $pathspec) {
					if (fnmatch($pathspec, $modifiedPathspec)) {
						if (
							// inclusion strategy
							$modifiedStatus == 'M' &&
							in_array('modified', $this->paths[$pathspec])
							||
							$modifiedStatus == 'A' &&
							in_array('added', $this->paths[$pathspec])
							||
							$modifiedStatus == 'D' &&
							in_array('deleted', $this->paths[$pathspec])
							||
							$modifiedStatus == 'R' &&
							in_array('renamed', $this->paths[$pathspec])
							||
							$modifiedStatus == 'C' &&
							in_array('copied', $this->paths[$pathspec])
							||
							$modifiedStatus == 'U' &&
							in_array('unmerged', $this->paths[$pathspec])
							||
							$modifiedStatus == '?' &&
							in_array('untracked', $this->paths[$pathspec])
							||

							// exclusion strategy
							$modifiedStatus == 'M' &&
							in_array('all', $this->paths[$pathspec]) &&
							!in_array('-modified', $this->paths[$pathspec])
							||
							$modifiedStatus == 'A' &&
							in_array('all', $this->paths[$pathspec]) &&
							!in_array('-added', $this->paths[$pathspec])
							||
							$modifiedStatus == 'D' &&
							in_array('all', $this->paths[$pathspec]) &&
							!in_array('-deleted', $this->paths[$pathspec])
							||
							$modifiedStatus == 'R' &&
							in_array('all', $this->paths[$pathspec]) &&
							!in_array('-renamed', $this->paths[$pathspec])
							||
							$modifiedStatus == 'C' &&
							in_array('all', $this->paths[$pathspec]) &&
							!in_array('-copied', $this->paths[$pathspec])
							||
							$modifiedStatus == 'U' &&
							in_array('all', $this->paths[$pathspec]) &&
							!in_array('-unmerged', $this->paths[$pathspec])
							||
							$modifiedStatus == '?' &&
							in_array('all', $this->paths[$pathspec]) &&
							!in_array('-untracked', $this->paths[$pathspec])
						) {
							return true;
						}
					}
				}
			}

			return false;
		}

		throw new \RuntimeException(sprintf('VCS type %s is not supported', get_class($environment->getVcs())));
	}
}
