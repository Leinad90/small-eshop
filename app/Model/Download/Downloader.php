<?php

declare(strict_types=1);

namespace App\Model\Download;


use App\Model\Utils\DateTime;
use Contributte\Http\Curl\CurlClient;
use Exception;
use GuzzleHttp\Psr7\HttpFactory;
use Nette\Iterators\CachingIterator;

abstract class Downloader
{

}

class DownloaderException extends Exception
{

}
