<?php

namespace Lvlapc;

use Lvlapc\Authentication\AdapterInterface;
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
	private const STATE_INITIAL = 0;

	private const STATE_SIGNED = 1;

	private const STATE_UNSIGNED = 2;
	/**
	 * @var AdapterInterface
	 */
	protected $tokenAdapter;
	/**
	 * @var int
	 */
	private $state = self::STATE_INITIAL;
	/**
	 * @var UserInterface
	 */
	private $user;

	/**
	 * Authentication constructor.
	 *
	 * @param AdapterInterface $tokenAdapter
	 */
	public function __construct(AdapterInterface $tokenAdapter)
	{
		$this->tokenAdapter = $tokenAdapter;
	}

	/**
	 * Authenticates user with UserProviderInterface and CredentialsCheckerInterface
	 *
	 * @param UserProviderInterface       $provider
	 * @param CredentialsCheckerInterface $checker
	 *
	 * @return bool
	 */
	public function signIn(UserProviderInterface $provider, CredentialsCheckerInterface $checker): bool
	{
		if ($this->state === self::STATE_SIGNED) {
			return true;
		}

		$this->state = self::STATE_UNSIGNED;

		if ($this->_eventsManager instanceof Manager) {
			$fire = $this->_eventsManager->fire('authentication:beforeAuthenticate', $this, null, false);

			if ($fire === false) {
				return false;
			}
		}

		$user = $provider->getUser();

		if ($user === null) {
			if ($this->_eventsManager instanceof Manager) {
				$this->_eventsManager->fire('authentication:userNotFound', $this, null, false);
			}

			return false;
		}

		if (!$checker->check($user)) {
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

		$this->user = $user;

		$this->state = self::STATE_SIGNED;

		return true;
	}

	/**
	 * Clearing authentication data
	 */
	public function signOut(): void
	{
		$this->user = null;

		if ($this->state === self::STATE_SIGNED) {
			$this->tokenAdapter->signOut();

			$this->state = self::STATE_UNSIGNED;
		}
	}

	/**
	 * Is user is signed in
	 *
	 * @return bool
	 */
	public function isSigned(): bool
	{
		if ($this->state === self::STATE_INITIAL) {
			$this->state = $this->tokenAdapter->isSigned() ? self::STATE_SIGNED : self::STATE_UNSIGNED;
		}

		return $this->state === self::STATE_SIGNED;
	}

	/**
	 * Retrieves user with last set UserProvider
	 *
	 * @return UserInterface
	 * @throws Exception
	 */
	public function getUser(): ?UserInterface
	{
		if (!$this->isSigned()) {
			return null;
		}

		if ($this->user === null) {

			$this->user = $this->tokenAdapter->getUser();

			if ($this->user === null) {

				$class = get_class($this->tokenAdapter);

				throw new Exception("{$class}::getUser has null returned");
			}
		}

		return $this->user;
	}
}