<?php declare(strict_types = 1);

namespace App\Model\Utils;

use Contributte\Utils\Strings as ContributteStrings;

 class Strings extends ContributteStrings
{
	public static function trimOrNull(?string $value, string $charlist = self::TrimCharacters): ?string
	{
		$value = static::trim((string)$value, $charlist);
		if(static::length($value) < 1) {
			return null;
		}
		return $value;
	}
}
