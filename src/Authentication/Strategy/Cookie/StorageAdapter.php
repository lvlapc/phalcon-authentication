<?php

namespace Lvlapc\Authentication\Strategy\Cookie;

use Lvlapc\Authentication\UserInterface;

interface StorageAdapter
{
	public function getUser(string $token): UserInterface;

	public function save(string $token, UserInterface $user): bool;

	public function delete(string $token): string;
}