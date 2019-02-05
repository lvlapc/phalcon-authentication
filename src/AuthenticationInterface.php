<?php

namespace Lvlapc;

use Lvlapc\Authentication\CredentialsCheckerInterface;
use Lvlapc\Authentication\UserInterface;
use Lvlapc\Authentication\UserProviderInterface;

/**
 * Class AuthenticationInterface
 *
 * @package Lvlapc
 */
interface AuthenticationInterface
{
	/**
	 * Sets an object which retrieves user from storage system
	 *
	 * @param UserProviderInterface $provider
	 *
	 * @return AuthenticationInterface
	 */
	public function setUserProvider(UserProviderInterface $provider): AuthenticationInterface;

	/**
	 * Sets an object which check user credentials
	 *
	 * @param CredentialsCheckerInterface $checker
	 *
	 * @return AuthenticationInterface
	 */
	public function setCredentialsChecker(CredentialsCheckerInterface $checker): AuthenticationInterface;

	/**
	 * Tells whether to use or not remember me option among sessions
	 *
	 * @param bool $use
	 *
	 * @return AuthenticationInterface
	 */
	public function useRememberMe(bool $use): AuthenticationInterface;

	/**
	 * Authenticates user with UserProvider and CredentialsChecker
	 *
	 * @return bool
	 */
	public function authenticate(): bool;

	/**
	 * Is user authenticated
	 *
	 * @return bool
	 */
	public function isLoggedIn(): bool;

	/**
	 * Clearing authentication data
	 */
	public function logout(): void;

	/**
	 * Retrieves user with last set UserProvider
	 *
	 * @return UserInterface
	 */
	public function getUser(): UserInterface;
}