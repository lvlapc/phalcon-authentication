<?php

namespace Lvlapc\Authentication\Adapter;

use Lvlapc\Authentication\AdapterInterface;
use Lvlapc\Authentication\Exception;
use Lvlapc\Authentication\UserInterface;

/**
 * Class Composition
 *
 * Allows to compose several token adapters
 *
 * @package Lvlapc\Authentication\Adapter
 */
class Composition implements AdapterInterface
{
	/**
	 * @var AdapterInterface[]
	 */
	protected $adapters = [];

	/**
	 * Composition constructor.
	 *
	 * @param AdapterInterface[] $adapters
	 *
	 * @throws Exception
	 */
	public function __construct(array $adapters)
	{
		if (empty($adapters)) {
			throw new Exception('You should provide at least one Adapter instance');
		}

		foreach ($adapters as $token) {
			$this->add($token);
		}
	}

	/**
	 *  Registers TokenAdapter
	 *
	 * @param AdapterInterface $adapter
	 *
	 * @return Composition
	 */
	public function add(AdapterInterface $adapter): Composition
	{
		if (in_array($adapter, $this->adapters, true)) {
			return $this;
		}

		$this->adapters[] = $adapter;

		return $this;
	}

	/**
	 * Authenticates all provided Adapters
	 *
	 * @param UserInterface $user
	 *
	 * @return bool
	 */
	public function signIn(UserInterface $user): bool
	{
		foreach ($this->adapters as $i => $adapter) {
			if (!$adapter->signIn($user)) {
				$this->signOut();

				return false;
			}
		}

		return true;
	}

	/**
	 * Clears authentication data
	 */
	public function signOut(): void
	{
		foreach ($this->adapters as $token) {
			$token->signOut();
		}
	}

	/**
	 * Finds first authenticated adapter
	 *
	 * @return bool
	 */
	public function isSigned(): bool
	{
		foreach ($this->adapters as $adapter) {
			if ($adapter->isSigned()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieves user
	 *
	 * @return UserInterface
	 */
	public function getUser(): ?UserInterface
	{
		foreach ($this->adapters as $adapter) {
			$user = $adapter->getUser();

			if ($user !== null) {
				return $user;
			}
		}

		return null;
	}
}