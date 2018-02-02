<?php
namespace App\Shop;

use App\{
    Auth\User,
    Shop\Entity\Product,
    Shop\Exception\AlreadyPurchasedException,
    Shop\Table\PurchaseTable,
    Shop\Table\StripeUserTable
};
use Framework\Api\Stripe;
use Staaky\VATRates\VATRates;
use Stripe\Card;
use Stripe\Customer;

/**
 * Class PurchaseProduct
 * @package App\Shop
 */
class PurchaseProduct
{
    /**
     * @var PurchaseTable
     */
    private $table;
    /**
     * @var Stripe
     */
    private $stripe;
    /**
     * @var StripeUserTable
     */
    private $stripeUserTable;

    /**
     * PurchaseProduct constructor.
     * @param PurchaseTable $table
     * @param Stripe $stripe
     * @param StripeUserTable $stripeUserTable
     */
    public function __construct(
        PurchaseTable $table,
        Stripe $stripe,
        StripeUserTable $stripeUserTable
    ) {
        $this->table = $table;
        $this->stripe = $stripe;
        $this->stripeUserTable = $stripeUserTable;
    }

    /**
     * Generates the product purchase for the user using Stripe.
     * @param Product $product
     * @param User $user
     * @param string $token
     * @throws AlreadyPurchasedException
     */
    public function process(Product $product, User $user, string $token): void
    {
        // Verifies that the user hadn't already purchased the product.
        if ($this->table->findEach($product, $user) !== null) {
            throw new AlreadyPurchasedException;
        }

        // Calculate the VAT rate and the price according to the country.
        $card = $this->stripe->getCardFromToken($token);
        $vatRate = (new VATRates())->getStandardRate($card->country) ?: 0;
        $price = floor($product->getPrice() * ((100 + $vatRate) / 100));

        // Creates or retreives the customer of the user.
        $customer = $this->findCustomerForUser($user, $token);


        // Creates or retreives the Stripe user card.
        $card = $this->getMatchingCard($customer, $card);
        if ($card === null) {
            $card = $this->stripe->setCardForCustomer($customer, $token);
        }

        // Generates the invoice of the user (charges).
        $charge = $this->stripe->setCharge([
            'amount' => $price * 100,
            'currency' => 'eur',
            'source' => $card->id,
            'customer' => $customer->id,
            'description' => "Purchase: {$product->getName()}"
        ]);

        // Saves the transaction in the database.
        $this->table->insert([
            'user_id' => $user->getId(),
            'product_id' => $product->getId(),
            'price' => $product->getPrice(),
            'vat' => $vatRate,
            'country' => $card->country,
            'created_at' => date('Y-m-d H:i:s'),
            'stripe_id' => $charge->id,
        ]);
    }

    /**
     * @param Customer $customer
     * @param Card $card
     * @return null|Card
     */
    private function getMatchingCard(Customer $customer, $card): ?Card
    {
        foreach ($customer->sources->data as $d) {
            if ($d->fingerprint === $card->fingerprint) {
                return $d;
            }
        }
        return null;
    }

    /**
     * Generates the customer from the user.
     * @param User $user
     * @param string $token
     * @return Customer
     */
    private function findCustomerForUser(User $user, string $token): Customer
    {
        $customerId = $this->stripeUserTable->findCustomerForUser($user);
        if ($customerId) {
            $customer = $this->stripe->getCustomer($customerId);
        } else {
            $customer = $this->stripe->setCustomer([
                'email' => $user->getEmail(),
                'source' => $token
            ]);
            $this->stripeUserTable->insert([
                'user_id' => $user->getId(),
                'customer_id' => $customer->id,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        return $customer;
    }
}
