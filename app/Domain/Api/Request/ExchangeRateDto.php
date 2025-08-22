<?php declare(strict_types = 1);

namespace App\Domain\Api\Request;

use App\Model\Utils\DateTime;
use Symfony\Component\Validator\Constraints as Assert;

final class ExchangeRateDto
{

	/** @Assert\NotBlank */
	public DateTime $date;

	public ?string $lang = null;

}
