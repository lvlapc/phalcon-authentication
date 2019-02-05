<?php

namespace Lvlapc\Authentication\Strategy;

use Lvlapc\Authentication\Strategy\Session\Adapter;
use Lvlapc\Authentication\Strategy\Session\Data\Simple;
use Lvlapc\Authentication\StrategyInterface;
use Lvlapc\Authentication\UserInterface;
use Lvlapc\Authentication\UserProviderInterface;

/**
 * Class Session
 *
 * @package Lvlapc\Authentication\InitializationStrategy
 */
class Session implements StrategyInterface
{
	/**
	 * @var Adapter
	 */
	protected $session;

	/**
	 * @var UserProviderInterface
	 */
	protected $userProvider;

	/**
	 * Session constructor.
	 *
	 * @param UserProviderInterface $userProvider
	 * @param Adapter               $session
	 */
	public function __construct(UserProviderInterface $userProvider, ?Adapter $session = null)
	{
		$this->userProvider = $userProvider;

		if ( $session === null ) {
			$session = new Adapter\PhalconSession();
		}

		$this->session = $session;
	}

	public function hasData(): bool
	{
		if ( !$this->session->hasData() ) {
			return false;
		}

		if ( !$this->session->get()->isValid() ) {
			$this->clear();

			return false;
		}

		return true;
	}

	public function getUser(): ?UserInterface
	{
		$data = $this->session->get();

		if ( $data === null || !$data->isValid() ) {
			return null;
		}

		return $this->userProvider->getUser($data->getId());
	}

	public function save(UserInterface $user): bool
	{
		$this->session->save(new Simple($user));

		return true;
	}

	public function clear(): bool
	{
		$this->session->delete();

		return true;
	}
}