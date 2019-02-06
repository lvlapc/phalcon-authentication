<?php

namespace Lvlapc\Authentication\Authenticator;

use Lvlapc\Authentication\AuthenticatorInterface;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Mvc\User\Component;

/**
 * Class Cookie
 *
 * @package Lvlapc\Authentication\Authenticator
 */
abstract class Cookie extends Component implements AuthenticatorInterface
{
	protected const KEY = 'rmt';

	/**
	 * @var UserInterface
	 */
	protected $user;

	public function createToken(UserInterface $user): bool
	{
		$token = $this->generateToken($user);

		$this->cookies->set(self::KEY, $token);

		return $this->createMapTokenToUser($token, $user);
	}

	public function checkToken(): bool
	{
		$user = $this->getUser();

		if ( $user === null ) {
			return false;
		}

		return $this->getToken() === $this->generateToken($user);
	}

	public function removeToken(): void
	{
		$this->deleteMapTokenToUser($this->getToken());

		$this->cookies->delete(self::KEY);
	}

	public function getUser(): UserInterface
	{
		if ( $this->user === null ) {
			$this->user = $this->findUserByToken($this->getToken());
		}

		return $this->user;
	}

	protected function generateToken(UserInterface $user): string
	{
		return sha1($user->getEmail() . $user->getPasswordHash() . $this->request->getUserAgent());
	}

	protected function getToken(): string
	{
		return $this->cookies->get(self::KEY)->getValue('string', '_');
	}

	abstract protected function findUserByToken(string $token): UserInterface;

	abstract protected function deleteMapTokenToUser(string $token): void;

	abstract protected function createMapTokenToUser(string $token, UserInterface $user): bool;
}