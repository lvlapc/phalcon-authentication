<?php

namespace Lvlapc\Authentication\CredentialsChecker;

use Lvlapc\Authentication\CredentialsCheckerInterface;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Mvc\User\Component;

/**
 * Class PhalconSecurity
 *
 * @package Lvlapc\Authentication\CredentialsChecker
 */
class PhalconSecurity extends Component implements CredentialsCheckerInterface
{
	/**
	 * @var string
	 */
	protected $password;

	/**
	 * PhalconSecurity constructor.
	 *
	 * @param string $password
	 */
	public function __construct(string $password)
	{
		$this->password = $password;
	}

	public function check(UserInterface $user): bool
	{
		return $this->security->checkHash($this->password, $user->getPasswordHash());
	}
}