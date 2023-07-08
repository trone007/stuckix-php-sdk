<?php

namespace Stuckix\Model;

final class Context
{
	public const CONTEXT_FILENAME = '[internal]';
	public const ANONYMOUS_CLASS_PREFIX = "anonymous@class\x00";
	private array $preContext = [];
	private ?string $contextLine = null;
	private array $postContext = [];

	/**
	 * @param string|null $functionName
	 * @param string $file
	 * @param int $line
	 * @param string|null $rawFunctionName
	 * @param string|null $absoluteFilePath
	 * @param array $variables
	 */
	public function __construct(
		private ?string $functionName,
		private string $file,
		private int $line,
		private ?string $rawFunctionName = null,
		private ?string $absoluteFilePath = null,
		private array $variables = [],
	)
	{	}

	/**
	 * @return array
	 */
	public function getPreContext(): array
	{
		return $this->preContext;
	}

	/**
	 * @param array $preContext
	 *
	 * @return Context
	 */
	public function setPreContext(array $preContext): Context
	{
		$this->preContext = $preContext;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getContextLine(): ?string
	{
		return $this->contextLine;
	}

	/**
	 * @param string|null $contextLine
	 *
	 * @return Context
	 */
	public function setContextLine(?string $contextLine): Context
	{
		$this->contextLine = $contextLine;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getPostContext(): array
	{
		return $this->postContext;
	}

	/**
	 * @param array $postContext
	 *
	 * @return Context
	 */
	public function setPostContext(array $postContext): Context
	{
		$this->postContext = $postContext;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getFunctionName(): ?string
	{
		return $this->functionName;
	}

	/**
	 * @param string|null $functionName
	 *
	 * @return Context
	 */
	public function setFunctionName(?string $functionName): Context
	{
		$this->functionName = $functionName;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFile(): string
	{
		return $this->file;
	}

	/**
	 * @param string $file
	 *
	 * @return Context
	 */
	public function setFile(string $file): Context
	{
		$this->file = $file;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLine(): int
	{
		return $this->line;
	}

	/**
	 * @param int $line
	 *
	 * @return Context
	 */
	public function setLine(int $line): Context
	{
		$this->line = $line;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getRawFunctionName(): ?string
	{
		return $this->rawFunctionName;
	}

	/**
	 * @param string|null $rawFunctionName
	 *
	 * @return Context
	 */
	public function setRawFunctionName(?string $rawFunctionName): Context
	{
		$this->rawFunctionName = $rawFunctionName;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getAbsoluteFilePath(): ?string
	{
		return $this->absoluteFilePath;
	}

	/**
	 * @param string|null $absoluteFilePath
	 *
	 * @return Context
	 */
	public function setAbsoluteFilePath(?string $absoluteFilePath): Context
	{
		$this->absoluteFilePath = $absoluteFilePath;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getVariables(): array
	{
		return $this->variables;
	}

	/**
	 * @param array $variables
	 *
	 * @return Context
	 */
	public function setVariables(array $variables): Context
	{
		$this->variables = $variables;

		return $this;
	}

	/**
	 * Gets whether the frame is internal.
	 */
	public function isInternal(): bool
	{
		return self::CONTEXT_FILENAME === $this->file;
	}
}
