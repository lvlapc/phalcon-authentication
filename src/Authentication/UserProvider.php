<?php

namespace Lvlapc\Authentication;

/**
 * Class UserProvider
 *
 * @package Lvlapc\Authentication
 */
abstract class UserProvider implements UserProviderInterface
{
	/**
	 * @var UserInterface
	 */
	protected $user;

	protected $isCalled = false;

	public function getUser(?string $id = null): ?UserInterface
	{
		if ( $this->isCalled ) {
			return $this->user;
		}

		$this->isCalled = true;

		$this->user = $this->findUser();

		return $this->user;
	}

	abstract protected function findUser(?string $id = null): UserInterface;
}