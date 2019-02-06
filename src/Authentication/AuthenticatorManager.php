<?php

namespace Lvlapc\Authentication;

/**
 * Class AuthenticatorManager
 *
 * @package Lvlapc\Authentication
 */
class AuthenticatorManager
{
	/**
	 * @var AuthenticatorInterface[]
	 */
	protected $authenticatorList;

	/**
	 * @var AuthenticatorInterface
	 */
	protected $signedAuthenticator;

	/**
	 *  Registers Authenticator
	 *
	 * @param string                 $name
	 * @param AuthenticatorInterface $authenticator
	 *
	 * @return AuthenticatorManager
	 */
	public function register(string $name, AuthenticatorInterface $authenticator): AuthenticatorManager
	{
		if ( in_array($authenticator, $this->authenticatorList) ) {
			return $this;
		}

		$this->authenticatorList[$name] = $authenticator;

		return $this;
	}

	/**
	 * Iterates over all registered authenticators and creates tokens
	 *
	 * @param UserInterface $user
	 *
	 * @throws Exception
	 */
	public function createTokens(UserInterface $user): void
	{
		/**
		 * @var $successful AuthenticatorInterface[]
		 */
		$successful = [];

		foreach ( $this->authenticatorList as $name => $authenticator ) {
			if ( $authenticator->createToken($user) ) {
				$successful[] = $authenticator;
			} else {
				foreach ( $successful as $item ) {
					$item->removeToken();
				}

				throw new Exception("Authenticator '{$name}' returned false while creating token");
			}
		}
	}

	public function isSigned(): bool
	{
		/**
		 * Authenticators which wants to be signed by the first found signed $authenticator
		 *
		 * @var $theyWantsAutoSign AuthenticatorInterface[]
		 */
		$theyWantsAutoSign = [];

		foreach ( $this->authenticatorList as $authenticator ) {
			if ( $authenticator->checkToken() ) {
				if ( $this->signedAuthenticator === null ) {
					$this->signedAuthenticator = $authenticator;
				}
			} else if ( $authenticator->wantsAutoSign() ) {
				$theyWantsAutoSign[] = $authenticator;
			}
		}

		if ( $this->signedAuthenticator === null ) {
			return false;
		}

		if ( !empty($theyWantsAutoSign) ) {
			$user = $this->signedAuthenticator->getUser();

			foreach ( $theyWantsAutoSign as $authenticator ) {
				$authenticator->createToken($user);
			}
		}

		return true;
	}

	public function removeTokens(): void
	{
		foreach ( $this->authenticatorList as $authenticator ) {
			$authenticator->removeToken();
		}
	}

	public function hasAuthenticators(): bool
	{
		return \count($this->authenticatorList) > 0;
	}

	public function getUser(): UserInterface
	{
		return $this->signedAuthenticator->getUser();
	}
}