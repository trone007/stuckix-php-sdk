<?php

declare(strict_types=1);

namespace Stuckix\Model;

final class EventIdentifier implements \Stringable
{
	private string $value;

	public function __construct(string $value)
	{
		$this->value = $value;
	}

	public static function generate(): self
	{
		return new self(\Ramsey\Uuid\Uuid::getFactory()->uuid6()->toString());
	}

	public function __toString(): string
	{
		return $this->value;
	}
}
