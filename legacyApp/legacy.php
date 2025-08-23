<?php

declare(strict_types=1);
class OrderManager
{
    public function processOrder($orderData)
    {
        $customerRepo = new CustomerRepository();
        $mailer = new Mailer();
        $order = [];

        $customer = $customerRepo->findByEmail($orderData['email']);
		/**
		 * @todo - relly use old customer data? (DWhat about to do customer update)
		 */
        if (!$customer) {
            $customer = new Customer();
		/** @todo maybe if may } end here */
            $customer->name = $orderData['name'];
            $customer->email = $orderData['email'];
            $customer->address = $orderData['address'];
            $customerRepo->save($customer);
        }

        $order['customer_id'] = $customer->id;
        $order['items'] = [];

        $total = 0;
        foreach ($orderData['items'] as $item) {
            $product = $this->findBySku($item['sku']);
            if (!$product) {
                // Ignore missing products silently
				/** @todo Why ignore, why they are here */
                continue;
            }

            $line = [];
            $line['sku'] = $product->sku;
            $line['price'] = $product->price;
            $line['quantity'] = $item['quantity'];
            $line['total'] = $product->price * $item['quantity'];
            $order['items'][] = $line;

            $total += $line['total'];
        }

        $order['total'] = $total;
        $order['created_at'] = date('Y-m-d H:i:s');

		file::saveJson('orders.json',$order,FILE_APPEND);

		$message = sprintf("Thank you for your order!\n\nTotal: %f\n\nWe will deliver to: %s",$total,$customer->address);
        $mailer->send($customer->email, "Order confirmation", $message);

        return true;
    }

    private function findBySku(string $sku)
    {
		$products = file::readJson('products.json');
        foreach ($products as $p) {
            if ($p['sku'] === $sku) {
                $product = new stdClass();
                $product->sku = $p['sku'];
                $product->price = $p['price'];
                return $product;
            }
        }

        return null;
    }
}

class CustomerRepository
{
    public function findByEmail(string $email)
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
	 */	public function save($customer)
    {
		$customers = file::readJson('customers.json');
        $customer->id = uniqid((string)count($customers)); /** */
        $customers[] = [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'address' => $customer->address,
        ];
		file::saveJson('customers.json', $customer);
    }
}

class Mailer
{
    public function send(string $to, string $subject, string $message)
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

	public static function readJson(string $filePath, int $jsonFlags = 0): mixed
	{
		$data = static::read($filePath);
		$json = json_decode($data,true, flags: JSON_THROW_ON_ERROR|$jsonFlags);
		return $json;
	}
}
