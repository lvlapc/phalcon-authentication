<?php

namespace Lvlapc\Authentication;

interface UserProviderInterface
{
	/**
	 * @param string|null $id
	 *
	 * @return UserInterface|null
	 */
	public function getUser(?string $id = null): ?UserInterface;
}