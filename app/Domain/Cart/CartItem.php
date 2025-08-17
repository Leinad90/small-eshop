<?php declare(strict_types = 1);

namespace App\Domain\Cart;

use App\Domain\Product\Product;
use App\Model\Database\Entity\AbstractEntity;
use App\Model\Database\Entity\TCreatedAt;
use App\Model\Database\Entity\TId;
use App\Model\Database\Entity\TUpdatedAt;
use App\Model\Exception\Logic\InvalidArgumentException;
use App\Model\Utils\DateTime;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Random;

/**
 * @ORM\Entity()
 * @ORM\Table(name="`CartItem`")
 * @ORM\HasLifecycleCallbacks
 */
class CartItem extends AbstractEntity
{

	use TId;
	use TCreatedAt;
	use TUpdatedAt;

	/**
 	* @ORM\JoinColumn(name="cart_id", nullable=false, onDelete="CASCADE")
	* @ORM\ManyToOne(targetEntity=Cart::class, inversedBy= "cartItems")
	 */
	public Cart $cart;

	/**
	 * @ORM\JoinColumn(name= "product_id", referencedColumnName= "id", nullable= false)
	 * @ORM\ManyToOne(targetEntity= Product::class)
	 */
	public Product $product;

	/**
	 * @ORM\Column(type= "integer", nullable= false)
	 */
	public int $quantity;

	public function getTotal(): float
	{
		return $this->quantity * $this->product->price;
	}

}
