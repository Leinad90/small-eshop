<?php declare(strict_types = 1);

namespace App\Module;

use Apitte\Core\Http\ApiRequest;
use Apitte\Core\Http\ApiResponse;
use App\Domain\Api\Response\ErrorDto;
use App\Model\Download\DownloaderException;
use App\Model\Download\ExchangeRare\ExchangeRate;
use App\Model\Utils\Strings;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use App\Domain\Api\Request\ExchangeRateDto;
use Apitte\Core\Annotation\Controller as Apitte;



/**
 * @Apitte\Path("/rates")
 * @Apitte\Id("rates")
 */
 class RatesController extends BaseController
{

	public function __construct(
		private readonly ExchangeRate $exchangeRateDownloader
	) {
	}

	/**
	  * @Apitte\OpenApi("
	  *   summary: Add product to cart
	  * ")
	  * @Apitte\Path("/")
	  * @Apitte\Method("POST")
	  * @Apitte\RequestBody(entity=ExchangeRateDto::class, required=true, validation=true)
	  */
	 public function get(ApiRequest $request, ApiResponse $response): ResponseInterface
	 {
		 $body = $request->getParsedBody();
		 try {
			 $data = $this->exchangeRateDownloader->download($body->date, Strings::upper($body->lang));
		 } catch (DownloaderException $e) {
			 return $response->withStatus(500,"download rates failed")->writeJsonObject(new ErrorDto($e->getMessage()));
		 }
		 return $response->writeJsonObject($data);
	 }

 }
