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
use ContaoCommunityAlliance\BuildSystem\Repositories\Exception\ActionException;
use ContaoCommunityAlliance\BuildSystem\Repositories\PlaceholderReplacer;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\Repository;
use ContaoCommunityAlliance\BuildSystem\Repository\GitRepository;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class JsonAction extends AbstractAction
{
	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @var array
	 */
	protected $settings;

	function __construct($file, array $settings)
	{
		$this->setFile($file);
		$this->setSettings($settings);
	}

	/**
	 * @param string $file
	 */
	public function setFile($file)
	{
		$this->file = (string) $file;
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
	 * @param array $schema
	 */
	public function setSettings(array $schema)
	{
		$this->settings = $schema;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getSettings()
	{
		return $this->settings;
	}

	/**
	 * {@inheritdoc}
	 */
	public function run(Environment $environment)
	{
		if ($this->condition && !$this->condition->accept($this, $environment)) {
			return;
		}

		$placeholderReplacer = $environment->getPlaceholderReplacer();

		$file = $placeholderReplacer->replace($this->file, $environment);
		$data = $placeholderReplacer->replace($this->settings['schema'], $environment);

		try {

			if (file_exists($file)) {
				$existingData = file_get_contents($file);
				$existingData = json_decode($existingData, true);

				$data = $this->merge($existingData, $data);
			}

			$data = json_encode($data);
			file_put_contents($file, $data);
		}
		catch (\Exception $e) {
			if (!isset($this->settings['ignoreFailure']) || !$this->settings['ignoreFailure']) {
				throw new ActionException('Json action failed', 0, $e);
			}
		}
	}

	/**
	 * Recursive merge the right array into the left array, but overwrite, not merge children.
	 *
	 * @param $left
	 * @param $right
	 *
	 * @return array
	 */
	protected function merge($left, $right)
	{
		if (is_array($right)) {
			if (!is_array($left)) {
				$left = array();
			}
			foreach ($right as $key => $value) {
				if (isset($left[$key])) {
					$left[$key] = $this->merge($left[$key], $value);
				}
				else {
					$left[$key] = $value;
				}
			}
			return $left;
		}
		else {
			return $right;
		}
	}
}
