<?php

namespace Lvlapc\Authentication;

interface UserInterface
{
	public function getId(): string ;

	public function getUserName(): string;

	public function getEmail(): string;

	public function getRole(): string;

	public function getPasswordHash(): string;
}