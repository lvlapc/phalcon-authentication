<?php

namespace Lvlapc\Authentication\UserProvider;

use Lvlapc\Authentication\UserInterface;
use Lvlapc\Authentication\UserProviderInterface;

/**
 * Class Pipe
 *
 * @package Lvlapc\Authentication\UserProvider
 */
class Pipe implements UserProviderInterface
{
	/**
	 * @var UserInterface
	 */
	protected $user;

	/**
	 * Pipe constructor.
	 *
	 * @param UserInterface $user
	 */
	public function __construct(UserInterface $user)
	{
		$this->user = $user;
	}

	/**
	 * @param string|null $id
	 *
	 * @return UserInterface|null
	 */
	public function getUser(?string $id = null): ?UserInterface
	{
		return $this->user;
	}
}