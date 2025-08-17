<?php declare(strict_types = 1);

namespace App\Module;

use Apitte\Core\Annotation\Controller as Apitte;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\Domain\Api\Response\CartContentDto;
use App\Domain\Cart\Cart;
use App\Domain\Cart\CartRepository;
use App\Model\Database\Repository\AbstractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @Apitte\Path("/cart")
 * @Apitte\Id("api-cart")
 */
 class CartController extends BaseController
{

	public function __construct(
		private EntityManagerInterface $entityManager
	) {

	}

	 /**
	  * @Apitte\OpenApi("
	  *   summary: Create a new cart
	  * ")
	  * @Apitte\Path("/")
	  * @Apitte\Method("GET")
	  */
	 public function create(ApiRequest $request, ApiResponse $response): ResponseInterface
	 {
		$cart = new Cart();
		 $this->entityManager->persist($cart);
		 $this->entityManager->flush();
		$id = $cart->getId();
		 return $response->writeJsonBody(
			 (array)$this->getCart($id)
		 );
	 }

	 /**
	  * @Apitte\OpenApi("
	  *   summary: get Cart content
	  * ")
	  * @Apitte\Path("/{id}")
	  * @Apitte\Method("GET")
	  */
	 public function get(ApiRequest $request, ApiResponse $response, int $id): ResponseInterface
	 {
		return $response->writeJsonBody(
			(array)$this->getCart($id)
		);
	 }

	 private function getCart(int $id): CartContentDto
	 {
		 $cart = $this->entityManager->getRepository(Cart::class)->find($id);
		$response = CartContentDto::from($cart);
		return $response;
	 }
}
