<?php
namespace App\Cart;

use App\{
	Auth\User, Cart\Table\CartTable, Cart\Table\OrderTable, Shop\Table\StripeUserTable
};
use Virton\Api\Stripe;
use Staaky\VATRates\VATRates;
use Stripe\Card;
use Stripe\Customer;

/**
 * Class PurchaseProduct
 * @package App\Shop
 */
class PurchaseCart
{
    /**
     * @var OrderTable
     */
    private $orderTable;

    /**
     * @var CartTable
     */
    private $cartTable;

    /**
     * @var Stripe
     */
    private $stripe;

    /**
     * @var StripeUserTable
     */
    private $stripeUserTable;

    public function __construct(
        OrderTable $orderTable,
        CartTable $cartTable,
        Stripe $stripe,
        StripeUserTable $stripeUserTable
    ) {
        $this->orderTable = $orderTable;
        $this->cartTable = $cartTable;
        $this->stripe = $stripe;
        $this->stripeUserTable = $stripeUserTable;
    }

    /**
     * Generates the cart purchase for the user using Stripe.
     * @param Cart $cart
     * @param User $user
     * @param string $token
     */
    public function process(Cart $cart, User $user, string $token): void
    {
        // Calculate the VAT rate and the price according to the country.
        $this->cartTable->hydrateCart($cart);
        $card = $this->stripe->getCardFromToken($token);
        $vatRate = (new VATRates())->getStandardRate($card->country) ?: 0;
        $price = floor($cart->getPrice() * ((100 + $vatRate) / 100));

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
            'description' => "Purchase on [Website]"
        ]);

        // Saves the transaction in the database.
        $this->orderTable->createFromCart($cart, [
            'user_id' => $user->getId(),
            'vat' => $vatRate,
            'country' => $card->country,
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
