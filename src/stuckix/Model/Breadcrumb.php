<?php

namespace Stuckix\Model;

final class Breadcrumb
{
	public const TYPE_DEFAULT = 'default';
	public const LEVEL_DEBUG = 'debug';

	public const LEVEL_INFO = 'info';

	public const LEVEL_WARNING = 'warning';

	public const LEVEL_ERROR = 'error';
	public const LEVEL_FATAL = 'fatal';

	private const ALLOWED_LEVELS = [
		self::LEVEL_DEBUG,
		self::LEVEL_INFO,
		self::LEVEL_WARNING,
		self::LEVEL_ERROR,
		self::LEVEL_FATAL,
	];

	/**
	 * @param string $level
	 * @param string $type
	 * @param string $category
	 * @param string|null $message
	 * @param array $metadata
	 * @param float|null $timestamp
	 *
	 * @throws \Exception
	 */
	public function __construct(
		private string $level,
		private string $type,
		private string $category,
		private ?string $message = null,
		private array $metadata = [],
		private ?float $timestamp = null
	)
	{
		if (!\in_array($level, self::ALLOWED_LEVELS, true))
		{
			throw new \Exception(
				'Value not allowed'
			);
		}
		$this->timestamp = $timestamp ?? microtime(true);
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function withType(string $type): self
	{
		if ($type === $this->type)
		{
			return $this;
		}

		$new = clone $this;
		$new->type = $type;

		return $new;
	}

	public function getLevel(): string
	{
		return $this->level;
	}

	/**
	 * @throws \Exception
	 */
	public function withLevel(string $level): self
	{
		if (!\in_array($level, self::ALLOWED_LEVELS, true))
		{
			throw new \Exception(
				'Value not allowed'
			);
		}

		if ($level === $this->level)
		{
			return $this;
		}

		$new = clone $this;
		$new->level = $level;

		return $new;
	}

	public function getCategory(): string
	{
		return $this->category;
	}

	public function withCategory(string $category): self
	{
		if ($category === $this->category)
		{
			return $this;
		}

		$new = clone $this;
		$new->category = $category;

		return $new;
	}

	public function getMessage(): ?string
	{
		return $this->message;
	}

	public function withMessage(string $message): self
	{
		if ($message === $this->message)
		{
			return $this;
		}

		$new = clone $this;
		$new->message = $message;

		return $new;
	}

	public function getMetadata(): array
	{
		return $this->metadata;
	}

	public function withMetadata(string $name, $value): self
	{
		if (isset($this->metadata[$name]) && $value === $this->metadata[$name])
		{
			return $this;
		}

		$new = clone $this;
		$new->metadata[$name] = $value;

		return $new;
	}

	public function withoutMetadata(string $name): self
	{
		if (!isset($this->metadata[$name]))
		{
			return $this;
		}

		$new = clone $this;

		unset($new->metadata[$name]);

		return $new;
	}

	public function getTimestamp(): float
	{
		return $this->timestamp;
	}

	public function withTimestamp(float $timestamp): self
	{
		if ($timestamp === $this->timestamp)
		{
			return $this;
		}

		$new = clone $this;
		$new->timestamp = $timestamp;

		return $new;
	}

	public static function fromArray(array $data): self
	{
		return new self(
			$data['level'],
			$data['type'] ?? self::TYPE_DEFAULT,
			$data['category'],
			$data['message'] ?? null,
			$data['data'] ?? [],
			$data['timestamp'] ?? null
		);
	}
}
