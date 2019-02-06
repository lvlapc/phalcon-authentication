<?php

namespace Lvlapc\Authentication;

interface UserProviderInterface
{
	/**
	 * Retrieves user from storage system
	 *
	 * @return UserInterface|null
	 */
	public function getUser(): ?UserInterface;
}