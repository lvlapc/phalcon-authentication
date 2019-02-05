<?php

namespace Lvlapc\Authentication;

interface StrategyInterface
{
	public function hasData(): bool;

	public function getUser(): ?UserInterface;

	public function save(UserInterface $user): bool;

	public function clear(): bool;
}