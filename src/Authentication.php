<?php

namespace Lvlapc;

use Lvlapc\Authentication\CredentialsChecker\WithoutPassword;
use Lvlapc\Authentication\CredentialsCheckerInterface;
use Lvlapc\Authentication\Exception;
use Lvlapc\Authentication\Strategy\Cookie;
use Lvlapc\Authentication\Strategy\Session;
use Lvlapc\Authentication\UserInterface;
use Lvlapc\Authentication\UserProvider\Pipe;
use Lvlapc\Authentication\UserProviderInterface;
use Phalcon\Events\Manager;
use Phalcon\Mvc\User\Component;

/**
 * Class Auth
 *
 * @package Lvlapc
 */
class Authentication extends Component implements AuthenticationInterface
{
	protected $_isLoggedIn    = false;

	protected $_useRememberMe = false;

	/**
	 * @var UserProviderInterface
	 */
	protected $userProvider;

	/**
	 * @var CredentialsCheckerInterface
	 */
	protected $credentialsChecker;

	/**
	 * @var Session
	 */
	protected $sessionStrategy;

	/**
	 * @var Cookie
	 */
	protected $cookieStrategy;

	/**
	 * Authentication constructor.
	 *
	 * @param Session $sessionStrategy
	 * @param Cookie  $cookieStrategy
	 */
	public function __construct(Session $sessionStrategy, Cookie $cookieStrategy)
	{
		$this->sessionStrategy = $sessionStrategy;
		$this->cookieStrategy  = $cookieStrategy;

		$this->initialize();
	}

	protected function initialize()
	{
		$this->_isLoggedIn = $this->isLoggedWithSession() || $this->isLoggedWithRememberCookie();
	}

	public function setUserProvider(UserProviderInterface $provider): AuthenticationInterface
	{
		$this->userProvider = $provider;

		return $this;
	}

	public function setCredentialsChecker(CredentialsCheckerInterface $checker): AuthenticationInterface
	{
		$this->credentialsChecker = $checker;

		return $this;
	}

	public function useRememberMe(bool $use = true): AuthenticationInterface
	{
		$this->_useRememberMe = $use;

		return $this;
	}

	/**
	 * Retrieves user from last set provider
	 *
	 * @return UserInterface
	 * @throws Exception
	 */
	public function getUser(): UserInterface
	{
		if ( !($this->userProvider instanceof UserProviderInterface) ) {
			throw new Exception('User provider have not set');
		}

		return $this->userProvider->getUser();
	}

	/**
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function authenticate(): bool
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
			$fire = $this->_eventsManager->fire('authentication:beforeAuthenticate', $this);

			if ( $fire === false ) {
				return false;
			}
		}

		if ( $this->credentialsChecker->check($user) ) {
			if ( $this->_eventsManager instanceof Manager ) {
				$this->_eventsManager->fire('authentication:incorrectPassword', $this, null, false);
			}

			return false;
		}

		$this->sessionStrategy->save($user);

		if ( $this->_useRememberMe ) {
			$this->cookieStrategy->save($user);
		}

		if ( $this->_eventsManager instanceof Manager ) {
			$this->_eventsManager->fire('authentication:afterAuthenticate', $this, null, false);
		}

		$this->_isLoggedIn = true;

		return true;
	}

	public function logout(): void
	{
		$this->_isLoggedIn = false;

		$this->sessionStrategy->clear();
		$this->cookieStrategy->clear();

		if ( $this->_eventsManager instanceof Manager ) {
			$this->_eventsManager->fire('authentication:logout', $this, null, false);
		}
	}

	public function isLoggedIn(): bool
	{
		return $this->_isLoggedIn;
	}

	protected function isLoggedWithSession(): bool
	{
		return $this->sessionStrategy->hasData();
	}

	protected function isLoggedWithRememberCookie()
	{
		if ( !$this->cookieStrategy->hasData() ) {
			return false;
		}

		$user = $this->cookieStrategy->getUser();

		if ( $user === null ) {
			return false;
		}

		return $this
			->setCredentialsChecker(new WithoutPassword())
			->setUserProvider(new Pipe($user))
			->authenticate();
	}
}