<?php

namespace Lvlapc\Authentication\Authenticator;

use Lvlapc\Authentication\AuthenticatorInterface;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Mvc\User\Component;

/**
 * Class Session
 *
 * @package Lvlapc\Authentication\Authenticator
 */
abstract class Session extends Component implements AuthenticatorInterface
{
	protected const KEY = 'auth';

	public function wantsAutoSign(): bool
	{
		return true;
	}

	public function createToken(UserInterface $user): bool
	{
		$this->session->set(self::KEY, [
			'id'        => $user->getId(),
			'userAgent' => $this->request->getUserAgent(),
		]);

		return true;
	}

	public function checkToken(): bool
	{
		if ( !$this->session->has(self::KEY) ) {
			return false;
		}

		$data = $this->getSessionData();

		return $data['userAgent'] === $this->request->getUserAgent();
	}

	public function removeToken(): void
	{
		$this->session->remove(self::KEY);
	}

	protected function getSessionData()
	{
		return $this->session->get(self::KEY, null) ?? ['id' => '', 'userAgent' => ''];
	}
}