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

namespace ContaoCommunityAlliance\BuildSystem\Repositories\Util;


/**
 * Match tags by a custom format.
 */
class TagMatcher
{
	protected $tag;

	protected $regexp;

	/**
	 * @param string $tag The tag syntax.
	 * @param string $regexp Optionally a regexp can be given, otherwise it will be generated from the tag syntax.
	 */
	public function __construct($tag, $regexp = null)
	{
		$this->tag = $tag;

		if ($regexp) {
			$this->regexp = $regexp;
		}
		else if (preg_match('~^([\w]).*\1$~', $tag)) {
			$this->regexp = $tag;
		}
		else {
			$parts = explode('*', $tag);
			$parts = array_map(
				function ($part) {
					return preg_quote($part, '~');
				},
				$parts
			);
			$this->regexp = '~' . implode('.*', $parts) . '~';
		}
	}

	/**
	 * @return string
	 */
	public function getTag()
	{
		return $this->tag;
	}

	/**
	 * @return string
	 */
	public function getRegexp()
	{
		return $this->regexp;
	}

	/**
	 * Evaluate the matcher on a given tag.
	 *
	 * @param $tag
	 *
	 * @return bool
	 */
	public function match($tag)
	{
		return (bool) preg_match($this->regexp, $tag);
	}
}
