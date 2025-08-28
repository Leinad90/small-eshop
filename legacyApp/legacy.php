<?php

declare(strict_types=1);
class OrderManager
{

	public function __construct(
		private readonly CustomerRepository $CustomerRepository,
		private readonly Mailer $Mailer,
		private readonly OrderRepository $OrderRepository,
	) {

	}

	/**
	 * @param array{name: string, email: string, address: string, items: array{sku: string, quantity: float}[] } $orderData
	 * @return void
	 */
    public function processOrder(array $orderData): void
    {

        $customer = $this->CustomerRepository->findByEmail($orderData['email']);
		/**
		 * @todo - relly use old customer data? (What about to do customer update)
		 */
        if ($customer===null) {
            $customer = new Customer();
		/** @todo maybe if may } end here */
            $customer->name = $orderData['name'];
            $customer->email = $orderData['email'];
            $customer->address = $orderData['address'];
            $this->CustomerRepository->save($customer);
        }

		$order = new Order();
		$order->customerId = $customer->id;
        foreach ($orderData['items'] as $item) {
            $product = $this->findBySku($item['sku']);
            if ($product===null) {
                // Ignore missing products silently
				/** @todo Why ignore, why they are here */
                continue;
            }

            $line = new OrderItem();
			$line->sku = $item['sku'];
            $line->price = $product->price;
            $line->quantity = $item['quantity'];
			$order->items[] = $line;

        }

        $order->createdAt = new DateTime();

		$this->OrderRepository->save($order);

		$message = sprintf("Thank you for your order!\n\nTotal: %f\n\nWe will deliver to: %s",$order->getTotal(),$customer->address);
        $this->Mailer->send($customer->email, "Order confirmation", $message);
	}

    private function findBySku(string $sku): ?stdClass
    {
		$products = file::readJson('products.json');
        foreach ($products as $p) {
            if ($p->sku === $sku) {
               return $p;
            }
        }

        return null;
    }
}

class OrderRepository
{
	public function save(Order $orderData) {
		file::addJson('orders.json', $orderData);
	}
}

class CustomerRepository
{
    public function findByEmail(string $email): ?Customer
    {
		$customers = file::readJson('customers.json');
       foreach ($customers as $c) {
            if ($c->email === $email) {
                $customer = new Customer();
                $customer->id = $c->id;
                $customer->name = $c->name;
                $customer->email = $c->email;
                $customer->address = $c->address;
                return $customer;
            }
        }

        return null;
    }

	/**
	 * @throws JsonException
	 */
	public function save(Customer $customer)
    {
		file::addJson('customers.json', $customer);
    }
}

class Mailer
{
    public function send(string $to, string $subject, string $message): void
    {
        // Simulate sending email
		$data = "[" . date('Y-m-d H:i:s') . "] To: $to\nSubject: $subject\n$message\n\n";
		file::save('email.log', $data, FILE_APPEND);
    }
}

class Customer implements ID
{
    public string $name;
    public string $email;
    public string $address;
}

class Order implements ID
{
	public string $customerId;

	/** @var OrderItem[] */
	public array $items = [];
	public DateTime $createdAt;

	public function getTotal(): float
	{
		$total = 0;
		foreach ($this->items as $item) {
			$total += $item->getTotal();
		}
		return $total;
	}

}

interface ID
{
	public string $id;
}

class OrderItem
{
	public string $sku;
	public float $quantity;
	public float $price;

	public function getTotal(): float
	{
		return $this->price * $this->quantity;
	}
}

class file
{
	public static function save(string $filePath, string $content, int $flag = 0): void
	{
		$written = file_put_contents($filePath,$content,$flag|LOCK_EX );
		if($written === false){
			throw new Exception("Unable to write to $filePath");
		}
		if($written < strlen($content)) {
			throw new Exception("Unable to write to $filePath");
		}
	}
	public static function saveJson(string $filePath, mixed $content, int $fileFlags = 0, int $jsonFlags = 0): void
	{
		$data = json_encode($content,$jsonFlags|JSON_THROW_ON_ERROR);
		static::save($filePath, $data, $fileFlags);
	}

	public static function read(string $filePath): string
	{
		$data = file_get_contents($filePath);
		if($data===false) {
			throw new Exception("Unable to read from $filePath");
		}
		return $data;
	}

	public static function readJson(string $filePath, bool $associative = false,  int $jsonFlags = 0): mixed
	{
		$data = static::read($filePath);
		$json = json_decode($data, $associative, flags: JSON_THROW_ON_ERROR|$jsonFlags);
		return $json;
	}

	public static function addJson(string $filePath, ID $data, int $jsonFlags = 0): void
	{
		$fp = fopen($filePath, "a");
		if(is_resource($fp) && flock($fp, LOCK_EX)) {
			$array = static::readJson($filePath, true, $jsonFlags);
			$data->id = uniqid((string)count($array));
			$array[] = $data;
			static::saveJson($filePath, $array);
		} else {
			throw new Exception("Could not obtain lock");
		}
		flock($fp, LOCK_UN);
	}
}
