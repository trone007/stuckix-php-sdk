<?php

declare(strict_types=1);

namespace Stuckix\Model;

final class EventType implements \Stringable
{
	private string $value;
	private static array $instances = [];

	private function __construct(string $value)
	{
		$this->value = $value;
	}

	public static function default(): self
	{
		return self::getInstance('default');
	}

	public static function event(): self
	{
		return self::getInstance('event');
	}

	public function __toString(): string
	{
		return $this->value;
	}

	private static function getInstance(string $value): self
	{
		if (!isset(self::$instances[$value]))
		{
			self::$instances[$value] = new self($value);
		}

		return self::$instances[$value];
	}
}
