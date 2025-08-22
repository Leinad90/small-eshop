<?php declare(strict_types = 1);

namespace App\Domain\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class AddProductToCartDto
{

	/** @Assert\NotBlank */
	public string $cartId;

	/** @Assert\NotBlank */
	public string $sku;

	/** @Assert\GreaterThan(0) */
	public int $quantity;

}
