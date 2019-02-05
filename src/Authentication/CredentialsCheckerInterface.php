<?php

namespace Lvlapc\Authentication;

interface CredentialsCheckerInterface
{
	public function check(UserInterface $user): bool;
}