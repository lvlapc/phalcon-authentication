<?php

namespace Lvlapc\Authentication\Strategy;

use Lvlapc\Authentication\Strategy\Cookie\Adapter;
use Lvlapc\Authentication\Strategy\Cookie\Adapter\PhalconCookie;
use Lvlapc\Authentication\Strategy\Cookie\StorageAdapter;
use Lvlapc\Authentication\StrategyInterface;
use Lvlapc\Authentication\UserInterface;

/**
 * Class Cookie
 *
 * @package Lvlapc\Authentication\InitializationStrategy
 */
class Cookie implements StrategyInterface
{

	/**
	 * @var Adapter
	 */
	protected $cookie;

	/**
	 * @var StorageAdapter
	 */
	protected $storage;

	/**
	 * Cookie constructor.
	 *
	 * @param StorageAdapter $storage
	 * @param Adapter        $cookie
	 */
	public function __construct(StorageAdapter $storage, ?Adapter $cookie = null)
	{
		$this->storage = $storage;

		if ( $cookie === null ) {
			$cookie = new PhalconCookie();
		}

		$this->cookie = $cookie;
	}

	public function hasData(): bool
	{
		return $this->cookie->hasCookie();
	}

	public function getUser(): UserInterface
	{
		$token = $this->cookie->getToken();

		$user = $this->storage->getUser($token);

		if ( $user === null ) {
			$this->clear();
		}

		if ( $this->cookie->makeToken($user) !== $token ) {
			$this->clear();

			return null;
		}

		return $user;
	}

	public function save(UserInterface $user): bool
	{
		return $this->storage->save($this->cookie->saveToken($user), $user);
	}

	public function clear(): bool
	{
		return $this->storage->delete($this->cookie->deleteToken());
	}
}