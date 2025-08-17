<?php declare(strict_types = 1);

namespace App\Domain\Api\Response;

use App\Domain\Cart\Cart;
use App\Domain\Cart\CartItem;
use App\Domain\User\User;
use DateTimeInterface;

final class CartItemDto
{

	public int $id;

	public int $quantity;

	public float $total;

	public static function from(CartItem $cartItem): static
	{
		$clone = new static();
		$clone->id = $cartItem->getId();
		$clone->quantity = $cartItem->quantity;
		$clone->total = $cartItem->getTotal();

		return $clone;
	}

}
