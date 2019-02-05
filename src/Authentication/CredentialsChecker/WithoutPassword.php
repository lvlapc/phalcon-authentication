<?php

namespace Lvlapc\Authentication\CredentialsChecker;

use Lvlapc\Authentication\CredentialsCheckerInterface;
use Lvlapc\Authentication\UserInterface;

/**
 * Class WithoutPassword
 *
 * @package Lvlapc\Authentication\CredentialsChecker
 */
class WithoutPassword implements CredentialsCheckerInterface
{
	public function check(UserInterface $user): bool
	{
		return true;
	}
}