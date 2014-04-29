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

namespace ContaoCommunityAlliance\BuildSystem\Repositories\Provider;

/**
 * Repositories provider authentication.
 */
interface AuthInterface
{
	const BASIC = 'basic';

	const ACCESS_TOKEN = 'access_token';

	const OAUTH = 'oauth';

	/**
	 * Return the authentication type.
	 *
	 * @return string
	 */
	public function getType();
}
