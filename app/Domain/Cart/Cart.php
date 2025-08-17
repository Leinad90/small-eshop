<?php declare(strict_types = 1);

namespace App\Domain\Cart;

use App\Model\Database\Entity\AbstractEntity;
use App\Model\Database\Entity\TCreatedAt;
use App\Model\Database\Entity\TId;
use App\Model\Database\Entity\TUpdatedAt;
use App\Model\Utils\DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Random;

/**
 * @ORM\Entity(repositoryClass=CartRepository::class)
 * @ORM\Table(name="`Cart`")
 * @ORM\HasLifecycleCallbacks
 */
class Cart extends AbstractEntity
{

	use TId;
	use TCreatedAt;
	use TUpdatedAt;

	/**
	 * @var Collection<CartItem> $cartItems
	 */
	/**
	 * @ORM\OneToMany(targetEntity="CartItem", mappedBy="cart", cascade={"persist", "remove"})
	 */
	public Collection $cartItems;

	public function __construct() {
		$this->cartItems = new ArrayCollection();
	}

	public function getItemsCount(): int
	{
		return $this->cartItems->count();
	}

	public function getTotalQuantity(): int
	{
		$return = 0;
		foreach ($this->cartItems as $cartItem) {
			$return += $cartItem->quantity;
		}
		return $return;
	}

	public function getTotal(): float
	{
		$return = 0;
		foreach ($this->cartItems as $cartItem) {
			$return += $cartItem->getTotal();
		}
		return $return;
	}

}
