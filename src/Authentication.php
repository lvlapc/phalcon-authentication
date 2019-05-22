<?php

namespace Lvlapc;

use Lvlapc\Authentication\CredentialsCheckerInterface;
use Lvlapc\Authentication\Exception;
use Lvlapc\Authentication\TokenEngine\Composition;
use Lvlapc\Authentication\TokenEngineInterface;
use Lvlapc\Authentication\UserInterface;
use Lvlapc\Authentication\UserProviderInterface;
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
	 * @var bool
	 */
	protected $isSigned = false;

	/**
	 * @var UserProviderInterface
	 */
	protected $userProvider;

	/**
	 * @var CredentialsCheckerInterface
	 */
	protected $credentialsChecker;

	/**
	 * @var Composition
	 */
	protected $tokenEngine;

	/**
	 * @var UserInterface
	 */
	protected $user;

	/**
	 * Authentication constructor.
	 *
	 * @param TokenEngineInterface $tokenEngine
	 */
	public function __construct(TokenEngineInterface $tokenEngine)
	{
		$this->tokenEngine = $tokenEngine;

		$this->isSigned = $this->tokenEngine->exists();
	}

	/**
	 * Sets an object which retrieves user from storage system
	 *
	 * @param UserProviderInterface $provider
	 *
	 * @return AuthenticationInterface
	 */
	public function setUserProvider(UserProviderInterface $provider): AuthenticationInterface
	{
		$this->userProvider = $provider;

		return $this;
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
	 * @return bool
	 *
	 * @throws Exception
	 */
	public function signIn(): bool
	{
		if ($this->isSigned) {
			return true;
		}

		if ($this->userProvider === null) {
			throw new Exception('You should set "UserProvider" before calling "signIn" method');
		}

		if ($this->credentialsChecker === null) {
			throw new Exception('You should set "CredentialsChecker" before calling "signIn" method');
		}

		$user = $this->userProvider->getUser();

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

		if (!$this->tokenEngine->create($user)) {
			return false;
		}

		$this->user = $user;

		$this->isSigned = true;

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
		$this->isSigned = false;

		$this->tokenEngine->remove();
	}

	/**
	 * Is user is signed in
	 *
	 * @return bool
	 */
	public function isSigned(): bool
	{
		return $this->isSigned;
	}

	/**
	 * Retrieves user with last set UserProvider
	 *
	 * @return UserInterface
	 */
	public function getUser(): ?UserInterface
	{
		if ($this->user === null) {
			if ($this->userProvider !== null) {
				$this->user = $this->userProvider->getUser();
			} else {
				$this->user = $this->tokenEngine->getUser();
			}
		}

		return $this->user;
	}
}