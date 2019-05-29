<?php

namespace Lvlapc\Authentication\Adapter\Cookie;

use Lvlapc\Authentication\UserInterface;

interface Storage
{
	public function getUser(string $token): ?UserInterface;

	public function save(string $token, UserInterface $user): bool;

	public function remove($token): void;
}