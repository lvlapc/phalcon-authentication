<?php

namespace Lvlapc\Authentication\Adapter;

use Closure;
use Lvlapc\Authentication\AdapterInterface;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Mvc\User\Component;

/**
 * Class Session
 *
 * @package Lvlapc\Authentication\Adapter
 */
class Session extends Component implements AdapterInterface
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
	 * @param string  $sessionKey
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
	public function signIn(UserInterface $user): bool
	{
		$this->signOut();

		$value = [
			'id'        => $user->getId(),
			'userAgent' => $this->request->getUserAgent(),
		];

		$this->session->set($this->sessionKey, $value);

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

		$userAgent = $this->request->getUserAgent();

		if ($this->getSessionData()['userAgent'] !== $userAgent) {
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
		if (!$this->isSigned()) {
			return null;
		}

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