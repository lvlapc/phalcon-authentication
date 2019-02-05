<?php

namespace Lvlapc\Authentication\Strategy\Session\Data;

use Lvlapc\Authentication\Strategy\Session\Data;
use Lvlapc\Authentication\UserInterface;
use Phalcon\Mvc\User\Component;

/**
 * Class Simple
 *
 * @package Lvlapc\Authentication\Strategy\Session\Data
 */
class Simple extends Component implements Data
{
	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $userAgent;

	/**
	 * Simple constructor.
	 *
	 * @param UserInterface $user
	 */
	public function __construct(UserInterface $user)
	{
		$this->id = (string) $user->getId();

		$this->userAgent = $this->request->getUserAgent();
	}

	public function getId(): string
	{
		return $this->id;
	}

	public function isValid(): bool
	{
		return $this->userAgent === $this->request->getUserAgent();
	}
}