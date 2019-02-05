<?php

namespace Lvlapc\Authentication\Strategy\Cookie\Adapter;

use Lvlapc\Authentication\Strategy\Cookie\Adapter;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Mvc\User\Component;

/**
 * Class PhalconCookie
 *
 * @package Lvlapc\Authentication\Strategy\Cookie\Adapter
 */
class PhalconCookie extends Component implements Adapter
{
	public function hasCookie(): bool
	{
		return $this->cookies->has('rmt');
	}

	public function getToken(): string
	{
		return $this->cookies->get('rmt')->getValue('string', '_');
	}

	public function saveToken(UserInterface $user): string
	{
		$token = $this->makeToken($user);

		$this->cookies->set('rmt', $token);

		return $token;
	}

	public function makeToken(UserInterface $user): string
	{
		return sha1($user->getEmail() . $user->getPasswordHash() . $this->request->getUserAgent());
	}

	public function deleteToken(): string
	{
		$token = $this->getToken();

		$this->cookies->delete('rmt');

		return $token;
	}
}