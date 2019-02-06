<?php

namespace Lvlapc;

use Lvlapc\Authentication\CredentialsCheckerInterface;
use Lvlapc\Authentication\UserInterface;
use Lvlapc\Authentication\UserProviderInterface;

interface AuthenticationInterface
{
	/**
	 * Sets an object which retrieves user from storage system
	 * (uses when user logs in on "login page")
	 *
	 * @param UserProviderInterface $provider
	 *
	 * @return AuthenticationInterface
	 */
	public function setUserProvider(UserProviderInterface $provider): AuthenticationInterface;

	/**
	 * Sets an object which check user credentials
	 * (uses when user logs in on "login page")
	 *
	 * @param CredentialsCheckerInterface $checker
	 *
	 * @return AuthenticationInterface
	 */
	public function setCredentialsChecker(CredentialsCheckerInterface $checker): AuthenticationInterface;

	/**
	 * Authenticates user with UserProvider and CredentialsChecker
	 *
	 * @return bool
	 */
	public function signIn(): bool;

	/**
	 * Is user authenticated
	 *
	 * @return bool
	 */
	public function isSigned(): bool;

	/**
	 * Clearing authentication data
	 */
	public function signOut(): void;

	/**
	 * Retrieves user with last set UserProvider
	 *
	 * @return UserInterface
	 */
	public function getUser(): UserInterface;
}