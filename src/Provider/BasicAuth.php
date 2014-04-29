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
class BasicAuth implements AuthInterface
{
	/**
	 * The basic authentication username.
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * The basic authentication password.
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($username, $password)
	{
		$this->username = (string) $username;
		$this->password = (string) $password;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getType()
	{
		return static::BASIC;
	}

	/**
	 * Set the username.
	 *
	 * @param string $username
	 */
	public function setUsername($username)
	{
		$this->username = (string) $username;
		return $this;
	}

	/**
	 * Return the username.
	 *
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Set the password.
	 *
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->password = (string) $password;
		return $this;
	}

	/**
	 * Return the password.
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}
}
