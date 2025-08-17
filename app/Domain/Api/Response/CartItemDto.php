<?php declare(strict_types = 1);

namespace App\Domain\Api\Response;

use App\Domain\Cart\Cart;
use App\Domain\Cart\CartItem;
use App\Domain\User\User;
use DateTimeInterface;

final class CartItemDto
{

	public ProductDto $product;

	public int $quantity;

	public float $total;

	public static function from(CartItem $cartItem): static
	{
		$clone = new static();
		$clone->product = ProductDto::from($cartItem->product);
		$clone->quantity = $cartItem->quantity;
		$clone->total = $cartItem->getTotal();

		return $clone;
	}

}
