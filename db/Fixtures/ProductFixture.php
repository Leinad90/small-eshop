<?php declare(strict_types=1);

namespace Database\Fixtures;

use App\Domain\Product\Product;
use Doctrine\Persistence\ObjectManager;

/**
 * @phpstan-type ProductArray array{sku: string, name: string, price: float,  description: ?string}
 */

class ProductFixture extends AbstractFixture
{
	private ObjectManager $manager;

	public function load(ObjectManager $manager): void
	{
		$this->manager = $manager;

		foreach ($this->getStaticProducts() as $product) {
			$this->saveProduct($product);
		}

		$this->manager->flush();
	}

	/**
	 * @phpstan-return ProductArray[]
	 */
	protected function getStaticProducts(): array
	{
		return [
			[
				'sku'=>'abc',
				'name'=>'Product 1',
				'description'=>'Product 1 description',
				'price'=>100,
			],
			[
				'sku'=>'123',
				'name'=>'Product 2',
				'description'=>'Product 2 description',
				'price'=>200,
			]
		];
	}

	/**
	 * @param ProductArray $product
	 */
	protected function saveProduct(array $product): void
	{
		$entity = new Product(
			$product['sku'],
			$product['name'],
			$product['price'],
			$product['description'],
		);


		$this->manager->persist($entity);
	}


}
