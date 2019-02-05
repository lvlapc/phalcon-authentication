<?php

namespace Lvlapc\Authentication\Strategy\Session;

interface Adapter
{
	public function hasData(): bool;

	public function get(): Data;

	public function save(Data $data): void;

	public function delete(): void;
}