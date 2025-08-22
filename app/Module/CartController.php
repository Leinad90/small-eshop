<?php declare(strict_types = 1);

namespace App\Module;

use Apitte\Core\Annotation\Controller as Apitte;
use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\Domain\Api\Request\AddProductToCartDto;
use App\Domain\Api\Response\CartContentDto;
use App\Domain\Api\Response\ErrorDto;
use App\Domain\Cart\AddToCartException;
use App\Domain\Cart\Cart;
use App\Domain\Cart\CartRepository;
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
	)
	{
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
		$cartContent = $this->getCart($cart->getId());
		if ($cartContent === null) {
			return $response->withStatus(500, 'Internal server error')->writeJsonObject(new ErrorDto('Internal server error'));
		}

		return $response->writeJsonBody((array) $cartContent);
	}

	/**
	 * @Apitte\OpenApi("
	 *   summary: get Cart content
	 * ")
	 * @Apitte\Path("/{id}")
	 * @Apitte\Method("GET")
	 */
	public function get(ApiRequest $request, ApiResponse $response): ResponseInterface
	{
		$id = $request->getParameter('id');
		$content = $this->getCart((int) $id);
		if ($content === null) {
			return $response->withStatus(400, 'Invalid cart id')->writeJsonObject(new ErrorDto('Cart id not found'));
		}

		return $response->writeJsonBody(
			(array) $this->getCart((int) $id)
		);
	}

	/**
	 * @Apitte\OpenApi("
	 *   summary: Add product to cart
	 * ")
	 * @Apitte\Path("/add")
	 * @Apitte\Method("POST")
	 * @Apitte\RequestBody(entity=AddProductToCartDto::class, required=false, validation=true)
	 */
	public function add(ApiRequest $request, ApiResponse $response): ResponseInterface
	{
		$body = $request->getParsedBody();
		$cartRepository = new CartRepository($this->entityManager, $this->entityManager->getClassMetadata(Cart::class));
		try {
			$cartRepository->addItemToCart($body->cartId, $body->sku, $body->quantity);
		} catch (AddToCartException $e) {
			return $response->withStatus(400, 'Not found')->writeJsonObject(new ErrorDto($e->getMessage()));
		}

		$this->entityManager->flush();

		return $response->writeJsonBody(
			(array) $this->getCart($body->cartId)
		);
	}

	private function getCart(int|string $id): ?CartContentDto
	{
		$cart = $this->entityManager->getRepository(Cart::class)->find($id);
		if ($cart === null) {
			return null;
		}

		return CartContentDto::from($cart);
	}

}
