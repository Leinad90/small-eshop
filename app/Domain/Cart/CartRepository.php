<?php declare(strict_types = 1);

namespace App\Domain\Cart;

use App\Domain\Product\Product;
use App\Model\Database\Repository\AbstractRepository;

/**
 * @method Cart|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Cart|NULL findOneBy($criteria, $orderBy = NULL)
 * @method Cart[] findAll()
 * @method Cart[] findBy($criteria, $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Cart>
 */
class CartRepository extends AbstractRepository
{

	/**
	 * @throws AddToCartException
	 */
	public function addItemToCart(string $cartId, string $sku, int $quantity): CartItem
	{
		$item = $this->findCartItem($cartId, $sku);
		if ($item === null) {
			$item = new CartItem();
			$item->cart = $this->_em->getRepository(Cart::class)->find($cartId);
			$item->product = $this->_em->getRepository(Product::class)->findOneBy(['sku' => $sku]);
			$item->quantity = 0;
		}

		$item->increaseQuantity($quantity);
		$this->_em->persist($item);

		return $item;
	}

	public function removeItemFromCart(string $cartId, string $sku, ?int $quantity = null): void
	{
		$item = $this->findCartItem($cartId, $sku);
		if ($item === null) {
			return;
		}

		if ($quantity) {
			$item->decreaseQuantity($quantity);
		}

		if ($item->quantity <= 0 || !$quantity) {
			$this->_em->remove($item);
		}
	}

	private function findCartItem(string $cartId, string $sku,): ?CartItem
	{
		$cart = $this->_em->getRepository(Cart::class)->find($cartId);
		if ($cart === null) {
			throw new AddToCartException('Cart not found');
		}

		$product = $this->_em->getRepository(Product::class)->findOneBy(['sku' => $sku]);
		if ($product === null) {
			throw new AddToCartException('Product not found');
		}

		return $this->_em->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'product' => $product]);
	}

}
