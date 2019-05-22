<?php

namespace Lvlapc\Authentication\TokenAdapter;

use Closure;
use Lvlapc\Authentication\UserInterface;
use Lvlapc\AuthenticationInterface;
use Phalcon\Mvc\User\Component;

/**
 * Class Session
 *
 * @package Lvlapc\Authentication\Authenticator
 */
class Session extends Component implements AuthenticationInterface
{
	/**
	 * @var string
	 */
	protected $sessionKey = 'auth';

	/**
	 * @var Closure
	 */
	protected $userProvider;

	/**
	 * Session constructor.
	 *
	 * @param Closure $userProvider
	 * @param string $sessionKey
	 */
	public function __construct(Closure $userProvider, string $sessionKey = 'auth')
	{
		$this->userProvider = $userProvider;
		$this->sessionKey   = $sessionKey;
	}

	/**
	 * Saves token to provided engine
	 *
	 * @param UserInterface $user
	 *
	 * @return bool
	 */
	public function signIn(?UserInterface $user): bool
	{
		if ($user === null) {
			return false;
		}

		if ($this->isSigned()) {
			return true;
		}

		$this->session->set($this->sessionKey, [
			'id'        => $user->getId(),
			'userAgent' => $this->request->getUserAgent(),
		]);

		return true;
	}

	/**
	 * Tries to check if token have been set
	 *
	 * @return bool
	 */
	public function isSigned(): bool
	{
		if (!$this->session->has($this->sessionKey)) {
			return false;
		}

		if ($this->getSessionData()['userAgent'] !== $this->request->getUserAgent()) {
			$this->signOut();

			return false;
		}

		return true;
	}

	/**
	 * Removes token from provided engine
	 */
	public function signOut(): void
	{
		$this->session->remove($this->sessionKey);
	}

	public function getUser(): ?UserInterface
	{
		return call_user_func($this->userProvider, $this->getSessionData()['id']);
	}

	protected function getSessionData()
	{
		$defaults = ['id' => '', 'userAgent' => ''];

		$stored = $this->session->get($this->sessionKey, null);

		if (empty($stored)) {
			return $defaults;
		}

		if (!is_scalar($stored['id']) || !is_scalar($stored['userAgent'])) {
			return $defaults;
		}

		return $stored;
	}
}