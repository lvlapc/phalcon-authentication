<?php

namespace Lvlapc\Authentication\TokenEngine;

use Lvlapc\Authentication\TokenEngineInterface;
use Lvlapc\Authentication\UserInterface;

/**
 * Class Composition
 *
 * @package Lvlapc\Authentication\TokenEngine
 */
class Composition implements TokenEngineInterface
{
	/**
	 * @var TokenEngineInterface[]
	 */
	protected $tokens      = [];

	protected $reverseInit = false;

	/**
	 * @var TokenEngineInterface
	 */
	protected $token;

	/**
	 * Composition constructor.
	 *
	 * @param TokenEngineInterface[] $tokens
	 * @param bool                   $reverseInit
	 */
	public function __construct(array $tokens, $reverseInit = false)
	{
		$this->reverseInit = $reverseInit;

		foreach ($tokens as $token) {
			$this->add($token);
		}
	}

	/**
	 *  Registers TokenEngine
	 *
	 * @param TokenEngineInterface $token
	 *
	 * @return Composition
	 */
	public function add(TokenEngineInterface $token): Composition
	{
		if (in_array($token, $this->tokens, true)) {
			return $this;
		}

		$this->tokens[] = $token;

		return $this;
	}

	/**
	 * Saves token to provided engine
	 *
	 * @param UserInterface $user
	 *
	 * @return bool
	 */
	public function create(UserInterface $user): bool
	{
		foreach ($this->tokens as $name => $token) {
			if (!$token->create($user)) {
				$this->remove();

				return false;
			}
		}

		return true;
	}

	/**
	 * Tries to check if token have been set
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		foreach ($this->tokens as $token) {
			if ($token->exists()) {
				$this->token = $token;
				break;
			}
		}

		if ($this->reverseInit && $this->token !== null) {
			foreach ($this->tokens as $token) {
				if ($token->exists()) {
					break;
				}

				$token->create($this->token->getUser());
			}
		}

		return $this->token !== null;
	}

	/**
	 * Removes token from provided engine
	 */
	public function remove(): void
	{
		foreach ($this->tokens as $token) {
			$token->remove();
		}
	}

	/**
	 * Retrieves user with UserProvider
	 *
	 * @return UserInterface
	 */
	public function getUser(): UserInterface
	{
		if ($this->token === null) {
			return null;
		}

		return $this->token->getUser();
	}
}