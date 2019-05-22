<?php

namespace Lvlapc;

use Lvlapc\Authentication\CredentialsCheckerInterface;
use Lvlapc\Authentication\Exception;
use Lvlapc\Authentication\TokenAdapterInterface;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\User\Component;

/**
 * Class Authentication
 *
 * @package Lvlapc
 */
class Authentication extends Component implements AuthenticationInterface
{
	/**
	 * @var CredentialsCheckerInterface
	 */
	protected $credentialsChecker;

	/**
	 * @var TokenAdapterInterface
	 */
	protected $tokenAdapter;

	/**
	 * Authentication constructor.
	 *
	 * @param TokenAdapterInterface $tokenAdapter
	 */
	public function __construct(TokenAdapterInterface $tokenAdapter)
	{
		$this->tokenAdapter = $tokenAdapter;
	}

	/**
	 * Sets an object which check user credentials
	 *
	 * @param CredentialsCheckerInterface $checker
	 *
	 * @return AuthenticationInterface
	 */
	public function setCredentialsChecker(CredentialsCheckerInterface $checker): AuthenticationInterface
	{
		$this->credentialsChecker = $checker;

		return $this;
	}

	/**
	 * Authenticates user with UserProvider and CredentialsChecker
	 *
	 * @param UserInterface|null $user
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function signIn(?UserInterface $user): bool
	{
		if ($this->isSigned()) {
			return true;
		}

		if ($this->credentialsChecker === null) {
			throw new Exception('You should set "CredentialsChecker" before calling "signIn" method');
		}

		if ($user === null) {
			if ($this->_eventsManager instanceof Manager) {
				$this->_eventsManager->fire('authentication:userNotFound', $this, null, false);
			}

			return false;
		}

		if ($this->_eventsManager instanceof Manager) {
			$fire = $this->_eventsManager->fire('authentication:beforeAuthenticate', $this, $user);

			if ($fire === false) {
				return false;
			}
		}

		if (!$this->credentialsChecker->check($user)) {
			if ($this->_eventsManager instanceof Manager) {
				$this->_eventsManager->fire('authentication:incorrectPassword', $this, $user, false);
			}

			return false;
		}

		if (!$this->tokenAdapter->signIn($user)) {
			return false;
		}

		if ($this->_eventsManager instanceof Manager) {
			$this->_eventsManager->fire('authentication:afterAuthenticate', $this, $user, false);
		}

		return true;
	}

	/**
	 * Clearing authentication data
	 */
	public function signOut(): void
	{
		$this->tokenAdapter->signOut();
	}

	/**
	 * Is user is signed in
	 *
	 * @return bool
	 */
	public function isSigned(): bool
	{
		return $this->tokenAdapter->isSigned();
	}

	/**
	 * Retrieves user with last set UserProvider
	 *
	 * @return UserInterface
	 */
	public function getUser(): ?UserInterface
	{
		if ($this->isSigned()) {
			return $this->tokenAdapter->getUser();
		}

		return null;
	}
}