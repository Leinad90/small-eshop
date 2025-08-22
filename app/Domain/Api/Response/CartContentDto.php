<?php declare(strict_types = 1);

namespace App\Domain\Api\Response;

use App\Domain\Cart\Cart;
use JsonSerializable;

final class CartContentDto implements JsonSerializable
{

	public int $id;

	/** @var CartItemDto[] $items */
	public array $items = [];

	public readonly int $item_count;

	public readonly int $total_quantity;

	public readonly float $total;

	public static function from(Cart $cart): static
	{
		$clone = new static();
		$clone->id = $cart->getId();
		$clone->item_count = $cart->getItemsCount();
		$clone->total_quantity = $cart->getTotalQuantity();
		$clone->total = $cart->getTotal();
		foreach ($cart->cartItems as $cartItem) {
			$clone->items[] = CartItemDto::from($cartItem);
		}

		return $clone;
	}

	/** @return mixed[] */
	public function jsonSerialize(): array
	{
		return (array) $this;
	}

}
