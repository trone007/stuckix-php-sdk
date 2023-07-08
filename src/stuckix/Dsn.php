<?php

namespace Stuckix;

final class Dsn implements \Stringable
{
	private function __construct(
		private string $scheme,
		private string $host,
		private int $port,
		private string $path,
		private string $token
	)
	{
	}

	public static function ofString(string $value): self
	{
		if (!($dsn = parse_url($value)))
		{
			throw new \InvalidArgumentException(sprintf('The "%s" DSN is invalid.', $value));
		}

		foreach (['scheme', 'host', 'path', 'user'] as $component)
		{
			if ((empty($dsn[$component])))
			{
				throw new \InvalidArgumentException(
					sprintf('The "%s" DSN must contain valid values.', $value)
				);
			}
		}

		if (!\in_array($dsn['scheme'], ['http', 'https']))
		{
			throw new \InvalidArgumentException('DSN must be http or https".');
		}

		$lastSlashPosition = strrpos($dsn['path'], '/');
		$path = $dsn['path'];

		if (false !== $lastSlashPosition)
		{
			$path = substr($dsn['path'], 0, $lastSlashPosition);
		}

		return new self(
			$dsn['scheme'],
			$dsn['host'],
			$dsn['port'] ?? ('http' === $dsn['scheme'] ? 80 : 443),
			$path,
			$dsn['user'],
		);
	}

	public function getScheme(): string
	{
		return $this->scheme;
	}

	public function getHost(): string
	{
		return $this->host;
	}

	public function getPort(): int
	{
		return $this->port;
	}

	public function getPath(): string
	{
		return $this->path;
	}

	public function getApiEndpointUrl(): string
	{
		return $this->getBaseEndpointUrl().'api/v1/project/' . $this->token . '/trace';
	}

	public function __toString(): string
	{
		return $this->getApiEndpointUrl();
	}

	private function getBaseEndpointUrl(): string
	{
		$url = $this->scheme.'://'. $this->host;

		if (('http' === $this->scheme && 80 !== $this->port) || ('https' === $this->scheme && 443 !== $this->port))
		{
			$url .= ':'.$this->port;
		}

		if (null !== $this->path)
		{
			$url .= $this->path;
		}

		return $url;
	}
}
