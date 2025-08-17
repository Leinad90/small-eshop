<?php declare(strict_types = 1);

namespace App\Domain\Cart;

use App\Domain\Product\Product;
use App\Model\Database\Repository\AbstractRepository;
use Doctrine\ORM\EntityRepository;
use InvalidArgumentException;

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
	 * @throws addToCardException
	 */
	public function addItemToCart(string $cartId, string $sku, int $quantity): CartItem
	{
		$cart = $this->_em->getRepository(Cart::class)->find($cartId);
		if($cart === null) {
			throw new addToCardException("Cart not found");
		}
		$product = $this->_em->getRepository(Product::class)->findOneBy(["sku"=>$sku]);
		if($product === null) {
			throw new addToCardException("Product not found");
		}
		$item = $this->_em->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'product' => $product]);
		if ($item===null) {
			$item = new CartItem();
			$item->cart = $cart;
			$item->product = $product;
			$item->quantity = 0;
		}
		$item->increaseQuantity($quantity);
		$this->_em->persist($item);
		return $item;
	}

}

class addToCardException extends InvalidArgumentException {

}
