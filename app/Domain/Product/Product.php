<?php declare(strict_types = 1);

namespace App\Domain\Product;

use App\Model\Database\Entity\AbstractEntity;
use App\Model\Database\Entity\TCreatedAt;
use App\Model\Database\Entity\TId;
use App\Model\Database\Entity\TUpdatedAt;
use App\Model\Exception\Logic\InvalidArgumentException;
use App\Model\Utils\DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="`Product`")
 * @ORM\HasLifecycleCallbacks
 */
class Product extends AbstractEntity
{

	use TId;
	use TCreatedAt;
	use TUpdatedAt;

	/** @ORM\Column(type="string", length=255, nullable=false, unique=true) */
	public string $sku;

	/**
	 * @ORM\Column(type="string", length=255, unique=false, nullable=false)
	 */
	public string $name;

	/**
	 * @ORM\Column(type= "float", nullable= false)
	 */
	public float $price;

	/**
	 * @ORM\Column(type= "string", length= 255, nullable= true)
	 */
	public ?string $description;

	public function __construct(string $sku, string $name, float $price, ?string $description = null)
	{
		$this->sku = $sku;
		$this->name = $name;
		$this->price = $price;
		if($description!==null) {
			$description = trim($description);
			if(strlen($description)==0) {
				$description=null;
			}
		}
		$this->description = $description;
	}

}
