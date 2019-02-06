<?php

namespace Lvlapc\Authentication;

/**
 * Interface AuthenticatorInterface
 *
 * Wrapped around sessions, cookies, http basic
 * The main purpose is to define either user signed or not, when Authentication service instantiates
 *
 * @package Lvlapc\Authentication
 */
interface AuthenticatorInterface
{
	/**
	 * If true it will be initialized from the first successfully checked authenticator
	 *
	 * @return bool
	 */
	public function wantsAutoSign(): bool;

	/**
	 * Saves token to provided engine
	 *
	 * @param UserInterface $user
	 *
	 * @return bool
	 */
	public function createToken(UserInterface $user): bool;

	/**
	 * Tries to check if token valid
	 *
	 * @return bool
	 */
	public function checkToken(): bool;

	/**
	 * Removes token from provided engine
	 */
	public function removeToken(): void;

	/**
	 * Retrieves user from the storage
	 *
	 * @return UserInterface
	 */
	public function getUser(): UserInterface;
}