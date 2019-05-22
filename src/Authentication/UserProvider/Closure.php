<?php

namespace Lvlapc\Authentication\UserProvider;

use Lvlapc\Authentication\UserInterface;
use Lvlapc\Authentication\UserProviderInterface;

/**
 * Class Callback
 *
 * @package Lvlapc\Authentication\UserProvider
 */
class Closure implements UserProviderInterface
{
	/**
	 * @var \Closure
	 */
	protected $closure;

	/**
	 * Closure constructor.
	 *
	 * @param \Closure $closure
	 * @param null     $newThis
	 */
	public function __construct(\Closure $closure, $newThis = null)
	{
		$this->closure = $closure;

		if (is_object($newThis)) {
			$closure->bindTo($newThis);
		}
	}

	public function getUser(?int $id = null): ?UserInterface
	{
		return call_user_func($this->closure, $id);
	}
}