<?php

namespace App\Cart\Table;

use App\Auth\User;
use App\Cart\Cart;
use App\Cart\CartRow;
use App\Cart\Entity\Order;
use App\Cart\Entity\OrderRow;
use App\Shop\Entity\Product;
use Virton\Database\Query;
use Virton\Database\QueryResult;
use Virton\Database\Table;

class OrderTable extends Table
{
	protected $table = 'orders';

	protected $entity = Order::class;

	/**
	 * @var OrderRowTable
	 */
	protected $orderRowTable;

	public function createFromCart(Cart $cart, array $params = [])
	{
		$params['price'] = $cart->getPrice();
		$params['created_at'] = date('Y-m-d H:i:s');

		$this->pdo->beginTransaction();
		$this->insert($params);
		$orderId = $this->getPdo()->lastInsertId();

		/** @var CartRow $row */
		foreach ($cart->getRows() as $row) {
			$this->getRowTable()->insert([
				'order_id' => $orderId,
				'price' => $row->getProduct()->getPrice(),
				'product_id' => $row->getProductId(),
				'quantity' => $row->getQuantity()
			]);
		}
		$this->pdo->commit();
	}

	private function getRowTable(): OrderRowTable
	{
		if ($this->orderRowTable === null) {
			$this->orderRowTable = new OrderRowTable($this->pdo);
		}
		return $this->orderRowTable;
	}

	public function findForUser(User $user): Query
	{
		return $this->makeQuery()->where("user_id = {$user->getId()}");
	}


	/**
	 * @param Order[] $orders
	 * @return null|QueryResult
	 */
	public function findRows($orders): ?QueryResult
	{
		$ordersId = [];
		foreach ($orders as $order) {
			$ordersId[] = $order->getId();
		}
		if (empty($ordersId)) {
			return null;
		};

		$rows = $this->getRowTable()->makeQuery()
			->where('o.order_id IN (' . implode(',', $ordersId) . ')')
			->join('products as p', 'p.id = o.product_id')
			->select('o.*', 'p.name as productName', 'p.slug as productSlug')
			->fetchAll();
		/** @var OrderRow $row */
		foreach ($rows as $row) {
			/** @var Order $order */
			foreach ($orders as $order) {
				if ($order->getId() === $row->getOrderId()) {
					$product = new Product();
					$product->setId($row->getProductId());
					$product->setName($row->productName);
					$product->setSlug($row->productSlug);
					$row->setProduct($product);
					$order->addRow($row);
					break;
				}
			}
		}
		return $rows;
	}

	/**
	 * @return null|string
	 */
	public function getMonthRevenue(): ?string
	{
		return $this->makeQuery()->select('SUM(price)')
			->where('o.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()')
			->fetchColumn();
	}
}
