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
    public function processOrder(array $orderData)
    {
        $order = [];

        $customer = $this->CustomerRepository->findByEmail($orderData['email']);
		/**
		 * @todo - relly use old customer data? (What about to do customer update)
		 */
        if (!$customer) {
            $customer = new Customer();
		/** @todo maybe if may } end here */
            $customer->name = $orderData['name'];
            $customer->email = $orderData['email'];
            $customer->address = $orderData['address'];
            $this->CustomerRepository->save($customer);
        }

		$order = new Order();
		$order->customerId = $customer->id;
		$items = [];
        $total = 0;
        foreach ($orderData['items'] as $item) {
            $product = $this->findBySku($item['sku']);
            if (!$product) {
                // Ignore missing products silently
				/** @todo Why ignore, why they are here */
                continue;
            }

            $line = new OrderItem();
			$line->sku = $item['sku'];
            $line->price = $product->price;
            $line->quantity = $item->quantity;
			$order->items[] = $line;

            $total += $line['total'];
        }

        $order->createdAt = new DateTime();

		$this->OrderRepository->save($order);

		$message = sprintf("Thank you for your order!\n\nTotal: %f\n\nWe will deliver to: %s",$total,$customer->address);
        $this->Mailer->send($customer->email, "Order confirmation", $message);

        return true;
    }

    private function findBySku(string $sku)
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
		$fp = fopen('orders.json', "a");
		if(flock($fp, LOCK_EX)) {
			$orders = file::readJson('orders.json',true);
			$orders[] = $orderData;
			file::saveJson('orders.json', $orders, FILE_APPEND);
		} else {
			throw new Exception("Could not obtain lock");
		}
		flock($fp,LOCK_UN);
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
		$customers = file::readJson('customers.json',true);
        $customer['id'] = uniqid((string)count($customers)); /** */
        $customers[] = [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'address' => $customer->address,
        ];
		file::saveJson('customers.json', $customers);
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

class Customer
{
    public $id;
    public $name;
    public $email;
    public $address;
}

class Order
{
	public string $id;
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
}
