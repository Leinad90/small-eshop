<?php declare(strict_types = 1);

namespace App\Model\Download\ExchangeRare;

use App\Model\Utils\DateTime;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

final class ExchangeRateDto implements JsonSerializable
{

	#[Assert\All([
		new Assert\Collection(
			fields: [
				'currencyCode'=>new Assert\Currency(),
				'amount'=>new Assert\Type('integer'),
				'rate'=>new Assert\Type('float'),
				'validFor'=>new Assert\Date(),
			],
			allowExtraFields: true
		)
	])]
	public array $rates;


	public function jsonSerialize(): mixed
	{
		return $this;
	}
}
