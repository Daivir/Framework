<?php
namespace Tests\App\Shop;

use App\Auth\User;
use App\Shop\{
    Entity\Product,
    Entity\Purchase,
    Exception\AlreadyPurchasedException,
    PurchaseProduct,
    Table\PurchaseTable,
    Table\StripeUserTable
};
use Framework\Api\Stripe;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Stripe\{
    Card,
    Charge,
    Collection,
    Customer
};

class PurchaseProductTest extends TestCase
{
    private $purchase;
    private $purchaseTable;
    private $stripe;
    private $stripeUserTable;

    public function setUp()
    {
        $this->purchaseTable = $this->prophesize(PurchaseTable::class);
        $this->stripe = $this->prophesize(Stripe::class);
        $this->stripeUserTable = $this->prophesize(StripeUserTable::class);
        $this->purchase = new PurchaseProduct(
            $this->purchaseTable->reveal(),
            $this->stripe->reveal(),
            $this->stripeUserTable->reveal()
        );
        $this->stripe->getCardFromToken(Argument::any())->will(function ($args) {
            $card = new Card();
            $card->id = "card-fakeid-111";
            $card->fingerprint = 'fake-fingerprint';
            $card->country = $args[0];
            return $card;
        });
    }

    public function testAlreadyPurchasedProduct()
    {
        $product = $this->makeProduct();
        $user = $this->makeUser();
        $this->purchaseTable->findEach($product, $user)
            ->shouldBeCalled()
            ->willReturn($this->makePurchase());
        $this->expectException(AlreadyPurchasedException::class);
        $this->purchase->process($product, $user, 'fake');
    }

    public function testPurchaseFrance()
    {
        $customerId = 'customer_id';
        $token = 'FR';

        $card = $this->makeCard('FR');
        $charge = $this->makeCharge();
        $customer = $this->makeCustomer();
        $product = $this->makeProduct();
        $user = $this->makeUser();

        $this->purchaseTable->findEach($product, $user)->willReturn(null);
        $this->stripeUserTable->findCustomerForUser($user)->willReturn($customerId);
        $this->stripe->getCustomer($customerId)->willReturn($customer);
        $this->stripe->setCardForCustomer($customer, $token)
            ->shouldBeCalled()
            ->willReturn($card);
        $this->stripe->setCharge(new Argument\Token\LogicalAndToken([
            Argument::withEntry('amount', (60 * 100)),
            Argument::withEntry('source', $card->id)
        ]))->shouldBeCalled()->willReturn($charge);
        $this->purchaseTable->insert([
            'user_id' => $user->getId(),
            'product_id' => $product->getId(),
            'price' => 50.00,
            'vat' => 20,
            'country' => 'FR',
            'created_at' => date('Y-m-d H:i:s'),
            'stripe_id' => $charge->id,
        ])->shouldBeCalled();
        $this->purchase->process($product, $user, $token);
    }

    public function testPurchaseUS()
    {
        $customerId = 'customer_id';
        $token = "US";

        $card = $this->makeCard();
        $charge = $this->makeCharge();
        $customer = $this->makeCustomer();
        $product = $this->makeProduct();
        $user = $this->makeUser();

        $this->purchaseTable->findEach($product, $user)->willReturn(null);
        $this->stripeUserTable->findCustomerForUser($user)->willReturn($customerId);
        $this->stripe->getCustomer($customerId)->willReturn($customer);
        $this->stripe->setCardForCustomer($customer, $token)
            ->shouldBeCalled()
            ->willReturn($card);
        $this->stripe->setCharge(new Argument\Token\LogicalAndToken([
            Argument::withEntry('amount', (50 * 100)),
            Argument::withEntry('source', $card->id)
        ]))->shouldBeCalled()->willReturn($charge);
        $this->purchaseTable->insert([
            'user_id' => $user->getId(),
            'product_id' => $product->getId(),
            'price' => 50.00,
            'vat' => 0,
            'country' => 'US',
            'created_at' => date('Y-m-d H:i:s'),
            'stripe_id' => $charge->id,
        ])->shouldBeCalled();
        $this->purchase->process($product, $user, $token);
    }

    public function testPurchaseWithExistingCard()
    {
        $customerId = 'customer_id';
        $token = "US";

        $card = $this->makeCard();
        $charge = $this->makeCharge();
        $customer = $this->makeCustomer([$card]);
        $product = $this->makeProduct();
        $user = $this->makeUser();

        $this->purchaseTable->findEach($product, $user)->willReturn(null);
        $this->stripeUserTable->findCustomerForUser($user)->willReturn($customerId);
        $this->stripe->getCustomer($customerId)->willReturn($customer);
        $this->stripe->setCardForCustomer($customer, $token)
            ->shouldNotBeCalled();
        $this->stripe->setCharge(new Argument\Token\LogicalAndToken([
            Argument::withEntry('amount', (50 * 100)),
            Argument::withEntry('source', $card->id),
            Argument::withEntry('customer', $customer->id)
        ]))->shouldBeCalled()->willReturn($charge);
        $this->purchaseTable->insert([
            'user_id' => $user->getId(),
            'product_id' => $product->getId(),
            'price' => 50.00,
            'vat' => 0,
            'country' => 'US',
            'created_at' => date('Y-m-d H:i:s'),
            'stripe_id' => $charge->id,
        ])->shouldBeCalled();
        $this->purchase->process($product, $user, $token);
    }

    public function testPurchaseWithNoneExistingCustomer()
    {
        $customerId = null;
        $token = "US";

        $card = $this->stripe->reveal()->getCardFromToken($token);
        $charge = $this->makeCharge();
        $customer = $this->makeCustomer([$card]);
        $product = $this->makeProduct();
        $user = $this->makeUser();

        $this->purchaseTable->findEach($product, $user)->willReturn(null);
        $this->stripeUserTable->findCustomerForUser($user)->willReturn($customerId);
        $this->stripeUserTable->insert([
            'user_id' => $user->getId(),
            'customer_id' => $customer->id,
            'created_at' => date('Y-m-d H:i:s')
        ])->shouldBeCalled();
        $this->stripe->setCustomer([
            'email' => $user->getEmail(),
            'source' => $token
        ])->shouldBeCalled()->willReturn($customer);
        $this->stripe->setCardForCustomer($customer, $token)
            ->shouldNotBeCalled();
        $this->stripe->setCharge(new Argument\Token\LogicalAndToken([
            Argument::withEntry('amount', (50 * 100)),
            Argument::withEntry('source', $card->id),
            Argument::withEntry('customer', $customer->id)
        ]))->shouldBeCalled()->willReturn($charge);
        $this->purchaseTable->insert([
            'user_id' => $user->getId(),
            'product_id' => $product->getId(),
            'price' => 50.00,
            'vat' => 0,
            'country' => 'US',
            'created_at' => date('Y-m-d H:i:s'),
            'stripe_id' => $charge->id,
        ])->shouldBeCalled();
        $this->purchase->process($product, $user, $token);
    }

    // TODO: creer un trait pour les tests de Stripe. (card/charge/customer)

    private function makeCard(string $country = 'US'): Card
    {
        $card = new Card;
        $card->id = "card-fakeid-110";
        $card->fingerprint = 'fake-fingerprint';
        $card->country = $country;
        return $card;
    }

    private function makeCharge(): Charge
    {
        $charge = new Charge;
        $charge->id = "charge-fakeid-110";
        return $charge;
    }

    private function makeCustomer(array $sources = []): Customer
    {
        $customer = new Customer();
        $customer->id = "customer-key-110";
        $collection = new Collection();
        $collection->data = $sources;
        $customer->sources = $collection;
        return $customer;
    }

    private function makeProduct(): Product
    {
        $product = new Product();
        $product->setId(3);
        $product->setPrice(50);
        return $product;
    }

    private function makePurchase(): Purchase
    {
        $purchase = new Purchase();
        $purchase->setId(3);
        return $purchase;
    }

    private function makeUser(): User
    {
        $user = new User();
        $user->setId(3);
        return $user;
    }
}
