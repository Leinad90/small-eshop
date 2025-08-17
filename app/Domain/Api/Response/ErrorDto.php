<?php declare(strict_types = 1);

namespace App\Domain\Api\Response;

use App\Domain\Cart\Cart;
use App\Domain\User\User;
use DateTimeInterface;
use JsonSerializable;

final class ErrorDto implements JsonSerializable
{

	public function __construct(public string $error)
	{

	}

	public function jsonSerialize(): array
	{
		return (array)$this;
	}
}
