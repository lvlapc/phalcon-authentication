<?php

namespace Lvlapc\Authentication\Strategy\Session\Adapter;

use Lvlapc\Authentication\Strategy\Session\Adapter;
use Lvlapc\Authentication\Strategy\Session\Data;
use Phalcon\Mvc\User\Component;

/**
 * Class PhalconSession
 *
 * @package Lvlapc\Authentication\Strategy\Session\Adapter
 */
class PhalconSession extends Component implements Adapter
{
	protected const SESSION_KEY = 'auth';

	public function hasData(): bool
	{
		return $this->session->has(self::SESSION_KEY);
	}

	public function get(): Data
	{
		$data = $this->session->get(self::SESSION_KEY, null);

		if ( !($data instanceof Data) ) {
			return null;
		}

		return $data;
	}

	public function save(Data $data): void
	{
		$this->session->set(self::SESSION_KEY, $data);
	}

	public function delete(): void
	{
		$this->session->remove(self::SESSION_KEY);
	}
}