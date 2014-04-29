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

namespace ContaoCommunityAlliance\BuildSystem\Repositories;

use ContaoCommunityAlliance\BuildSystem\NoOpLogger;
use ContaoCommunityAlliance\BuildSystem\Repositories\Provider\Repository;
use ContaoCommunityAlliance\BuildSystem\Repository\GitRepository;
use Guzzle\Http\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Repositories manager.
 */
class PlaceholderReplacer
{
	/**
	 * Replace placeholders in the input.
	 *
	 * @param string        $input
	 * @param Repository    $repository
	 * @param GitRepository $vcs
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function replace($input, Environment $environment)
	{
		$searchReplace = array();

		if ($environment->hasRepository()) {
			$searchReplace['%type%']  = $environment->getRepository()->getType();
			$searchReplace['%owner%'] = $environment->getRepository()->getOwner();
			$searchReplace['%name%']  = $environment->getRepository()->getName();

			foreach (parse_url($environment->getRepository()->getReadUrl()) as $key => $value) {
				$searchReplace['%' . $key . '%'] = $value;
			}
		}

		if ($environment->getPath()) {
			$searchReplace['%path%'] = $environment->getPath();
		}

		if ($environment->getVcs() instanceof GitRepository) {

		}

		return preg_replace_callback(
			'~%([^%]+)%~',
			function ($matches) use ($environment) {
				$parts = explode(':', $matches[1]);
				switch ($parts[0]) {
					case 'scheme':
						return parse_url($environment->getRepository()->getReadUrl(), PHP_URL_SCHEME);
					case 'host':
						return parse_url($environment->getRepository()->getReadUrl(), PHP_URL_HOST);
					case 'port':
						return parse_url($environment->getRepository()->getReadUrl(), PHP_URL_PORT);
					case 'user':
						return parse_url($environment->getRepository()->getReadUrl(), PHP_URL_USER);
					case 'pass':
						return parse_url($environment->getRepository()->getReadUrl(), PHP_URL_PASS);
					case 'path':
						return parse_url($environment->getRepository()->getReadUrl(), PHP_URL_PATH);
					case 'query':
						return parse_url($environment->getRepository()->getReadUrl(), PHP_URL_QUERY);
					case 'fragment':
						return parse_url($environment->getRepository()->getReadUrl(), PHP_URL_FRAGMENT);

					case 'type':
						return $environment->getRepository()->getType();
					case 'repository':
						return sprintf('%s/%s', $environment->getRepository()->getOwner(), $environment->getRepository()->getName());
					case 'owner':
						return $environment->getRepository()->getOwner();
					case 'name':
						return $environment->getRepository()->getName();
					case 'remote':
						return $environment->getRepository()->getRemoteName();
					case 'branch':
						return $environment->getRepository()->getRef();
					case 'read-url':
						return $environment->getRepository()->getReadUrl();
					case 'write-url':
						return $environment->getRepository()->getWriteUrl();
					case 'web-url':
						return $environment->getRepository()->getWebUrl();

					case 'dir':
						if ($environment->getPath()) {
							return $environment->getPath();
						}
						break;

					case 'ref':
						return $environment->getRepository()->getRef();
					case 'real-ref':
						return $environment->getRepository()->getRealRef();
					case 'ref-type':
						return $environment->getRepository()->getRefType();

					case 'tag':
						if ($environment->getVcs() instanceof GitRepository) {
							return $environment->getVcs()->describe(GitRepository::DESCRIBE_ALL);
						}
						break;
					case 'commit':
						if ($environment->getVcs() instanceof GitRepository) {
							return $environment->getVcs()->revParse();
						}
						break;

					case 'date':
						$format = isset($parts[1]) ? $parts[1] : 'c';
						return date($format);
				}

				$variables = $environment->getConfiguration()->getVariables();
				if ($variables && isset($variables[$parts[0]])) {
					return $this->replace($variables[$parts[0]], $environment);
				}

				throw new \InvalidArgumentException(sprintf('The token "%s" does not exists', $matches[1]));
			},
			$input
		);
	}
}
