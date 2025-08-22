<?php declare(strict_types = 1);

namespace App\Domain\Api\Response;

use JsonSerializable;

final class ErrorDto implements JsonSerializable
{

	public function __construct(public string $error)
	{
	}

	/**
	 * @return array{error: string}
	 */
	public function jsonSerialize(): array
	{
		return (array) $this;
	}

}
