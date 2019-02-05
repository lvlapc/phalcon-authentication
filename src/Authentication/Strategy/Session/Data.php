<?php

namespace Lvlapc\Authentication\Strategy\Session;

interface Data
{
	public function getId(): string;

	public function isValid(): bool;
}