<?php declare(strict_types = 1);

namespace App\Model\Download\ExchangeRare;

use App\Model\Download\Downloader;
use App\Model\Download\DownloaderException;
use Contributte\Http\Curl\CurlClient;
use DateTimeInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ExchangeRate extends Downloader
{

	public function __construct(
		protected string $url,
		protected SerializerInterface $serializer,
		protected ValidatorInterface $validator,
	)
	{
	}

	public function download(DateTimeInterface $dateTime, ?string $lang = null): ExchangeRateDto
	{
		$uri = $this->url . '?' . (http_build_query(array_filter(['date' => $dateTime->format('Y-m-d'), 'lang' => $lang])));
		$curlClient = new CurlClient();
		$response = $curlClient->makeRequest($uri);
		if ( !$response->isOk()) {
			throw new DownloaderException('Request failed');
		}

		if ($response->getError()) {
			throw new DownloaderException('Request failed');
		}

		return $this->serializer->deserialize($response->getBody(), ExchangeRateDto::class, 'json');
	}

}
