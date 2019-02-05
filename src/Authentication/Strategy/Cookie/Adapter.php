<?php

namespace Lvlapc\Authentication\Strategy\Cookie;

use Lvlapc\Authentication\UserInterface;

interface Adapter
{
	public function hasCookie(): bool;

	public function getToken(): string;

	public function saveToken(UserInterface $user): string;

	public function deleteToken(): string;

	public function makeToken(UserInterface $user): string;
}