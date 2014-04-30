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
class AccessTokenAuth implements AuthInterface
{
	/**
	 * The basic authentication username.
	 *
	 * @var string
	 */
	protected $accessToken;

	/**
	 * @param string $accessToken
	 */
	public function __construct($accessToken)
	{
		$this->accessToken = (string) $accessToken;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getType()
	{
		return static::ACCESS_TOKEN;
	}

	/**
	 * Set the username.
	 *
	 * @param string $username
	 */
	public function setAccessToken($username)
	{
		$this->accessToken = (string) $username;
		return $this;
	}

	/**
	 * Return the username.
	 *
	 * @return string
	 */
	public function getAccessToken()
	{
		return $this->accessToken;
	}
}
