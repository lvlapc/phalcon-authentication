<?php

namespace Lvlapc\Authentication\CredentialsChecker;

use Lvlapc\Authentication\CredentialsCheckerInterface;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Mvc\User\Component;

/**
 * Class Security
 *
 * @package Lvlapc\Authentication\CredentialsChecker
 */
class Security extends Component implements CredentialsCheckerInterface
{
	/**
	 * @var string
	 */
	protected $password;

	/**
	 * Security constructor.
	 *
	 * @param string $password
	 */
	public function __construct(string $password = '')
	{
		$this->password = $password;
	}

	public function check(UserInterface $user): bool
	{
		$password = empty($this->password) ? $this->request->getPost('password', ['string', 'trim']) : $this->password;

		return $this->security->checkHash($password, $user->getPassword());
	}
}