<?php

namespace Lvlapc;

use Lvlapc\Authentication\AuthenticatorManager;
use Lvlapc\Authentication\CredentialsCheckerInterface;
use Lvlapc\Authentication\Exception;
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
	 * @var AuthenticatorManager
	 */
	protected $authenticatorManager;

	/**
	 * @var UserInterface
	 */
	protected $user;

	/**
	 * Authentication constructor.
	 *
	 * @param AuthenticatorManager $authenticatorManager
	 *
	 * @throws Exception
	 */
	public function __construct(AuthenticatorManager $authenticatorManager)
	{
		if ( !$authenticatorManager->hasAuthenticators() ) {
			throw new Exception('AuthenticatorManager should have at least one Authenticator');
		}

		$this->authenticatorManager = $authenticatorManager;

		$this->isSigned = $this->authenticatorManager->isSigned();
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
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function signIn(): bool
	{
		if ( $this->userProvider === null ) {
			throw new Exception('You should set "UserProvider" before calling "authenticate" method');
		}

		if ( $this->credentialsChecker === null ) {
			throw new Exception('You should set "CredentialsChecker" before calling "authenticate" method');
		}

		$user = $this->userProvider->getUser();

		if ( $user === null ) {
			if ( $this->_eventsManager instanceof Manager ) {
				$this->_eventsManager->fire('authentication:userNotFound', $this, null, false);
			}

			return false;
		}

		if ( $this->_eventsManager instanceof Manager ) {
			$fire = $this->_eventsManager->fire('authentication:beforeAuthenticate', $this, $user);

			if ( $fire === false ) {
				return false;
			}
		}

		if ( !$this->credentialsChecker->check($user) ) {
			if ( $this->_eventsManager instanceof Manager ) {
				$this->_eventsManager->fire('authentication:incorrectPassword', $this, $user, false);
			}

			return false;
		}

		$this->authenticatorManager->createTokens($user);

		$this->user = $user;

		$this->isSigned = true;

		if ( $this->_eventsManager instanceof Manager ) {
			$this->_eventsManager->fire('authentication:afterAuthenticate', $this, $user, false);
		}

		return true;
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
	 * Clearing authentication data
	 */
	public function signOut(): void
	{
		$this->isSigned = false;

		$this->authenticatorManager->removeTokens();
	}

	/**
	 * Retrieves user from AuthenticatorManager
	 *
	 * @return UserInterface
	 */
	public function getUser(): UserInterface
	{
		if ( $this->user === null ) {
			$this->user = $this->authenticatorManager->getUser();
		}

		return $this->user;
	}
}