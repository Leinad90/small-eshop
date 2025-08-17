<?php declare(strict_types = 1);

namespace App\Domain\Api\Response;

use App\Domain\Cart\Cart;
use App\Domain\Product\Product;
use App\Domain\User\User;
use DateTimeInterface;
use JsonSerializable;

final class ProductDto implements JsonSerializable
{

	public string $sku;

	public string $name="";

	public float $price;

	public ?string $description;

	public readonly float $total;

	public static function from(Product $product): static
	{
		$clone = new static();
		$clone->sku = $product->sku;
		$clone->name = $product->name;
		$clone->price = $product->price;
		$clone->description = $product->description;
		return $clone;
	}

	public function jsonSerialize(): array
	{
		return (array)$this;
	}
}
