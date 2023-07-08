<?php

namespace Stuckix\Model;

final class Level implements \Stringable
{
	public const DEBUG = 'debug';
	public const INFO = 'info';
	public const WARNING = 'warning';
	public const ERROR = 'error';
	public const FATAL = 'fatal';
	public const ALLOWED_SEVERITIES = [
		self::DEBUG,
		self::INFO,
		self::WARNING,
		self::ERROR,
		self::FATAL,
	];
	private string $value;

	/**
	 * Constructor.
	 *
	 * @param string $value The value this instance represents
	 */
	public function __construct(string $value = self::INFO)
	{
		if (!\in_array($value, self::ALLOWED_SEVERITIES, true))
		{
			throw new \InvalidArgumentException(sprintf('The "%s" is not a valid enum value.', $value));
		}

		$this->value = $value;
	}

	/**
	 * Translate a PHP Error constant into a Sentry log level group.
	 *
	 * @param int $level PHP E_* error constant
	 *
	 * @return Level
	 */
	public static function fromError(int $level): self
	{
		return match ($level)
		{
			\E_DEPRECATED, \E_USER_DEPRECATED, \E_WARNING, \E_USER_WARNING => self::warning(),
			\E_ERROR, \E_PARSE, \E_CORE_ERROR, \E_CORE_WARNING, \E_COMPILE_ERROR, \E_COMPILE_WARNING => self::fatal(),
			\E_NOTICE, \E_USER_NOTICE, \E_STRICT => self::info(),
			default => self::error(),
		};
	}

	/**
	 * Creates a new instance of this enum for the "debug" value.
	 */
	public static function debug(): self
	{
		return new self(self::DEBUG);
	}

	/**
	 * Creates a new instance of this enum for the "info" value.
	 */
	public static function info(): self
	{
		return new self(self::INFO);
	}

	public static function warning(): self
	{
		return new self(self::WARNING);
	}

	public static function error(): self
	{
		return new self(self::ERROR);
	}

	public static function fatal(): self
	{
		return new self(self::FATAL);
	}

	public function isEqualTo(self $other): bool
	{
		return $this->value === (string)$other;
	}

	public function __toString(): string
	{
		return $this->value;
	}
}
