<?php

namespace Stuckix\Model;

final class Stacktrace
{
	/**
	 * @var Context[]
	 */
	private array $contexts = [];

	public function __construct(array $contexts)
	{
		if (empty($contexts))
		{
			throw new \InvalidArgumentException('Empty context');
		}

		foreach ($contexts as $context)
		{
			if (!$context instanceof Context)
			{
				throw new \UnexpectedValueException(
					sprintf(
						'Unexpected an instance of the "%s" class. Got: "%s".',
						Context::class,
						get_debug_type($context)
					)
				);
			}
		}

		$this->contexts = $contexts;
	}

	public function getContexts(): array
	{
		return $this->contexts;
	}

	public function getContext(int $index): Context
	{
		if ($index < 0 || $index >= count($this->contexts))
		{
			throw new \OutOfBoundsException();
		}

		return $this->contexts[$index];
	}

	public function addContext(Context $context): void
	{
		array_unshift($this->contexts, $context);
	}

	public function removeContext(int $index): void
	{
		if (count($this->contexts) <= 1)
		{
			throw new \RuntimeException('Cannot remove all contexts from the stacktrace.');
		}

		if (!isset($this->contexts[$index]))
		{
			throw new \OutOfBoundsException(sprintf('Cannot remove the context at index %d.', $index));
		}

		array_splice($this->contexts, $index, 1);
	}
}
