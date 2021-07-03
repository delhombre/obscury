<?php

namespace App\Service\Cart;

use App\Repository\SubscriptionRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    protected $session;
    protected $SubscriptionRepository;

    public function __construct(SessionInterface $session, SubscriptionRepository $SubscriptionRepository)
    {
        $this->session = $session;
        $this->SubscriptionRepository = $SubscriptionRepository;
    }

    public function add(int $id)
    {
        $panier = $this->session->get('panier', []);

        if (!empty($panier[$id])) {
            $panier[$id]++;
        } else {
            $panier[$id] = 1;
        }

        $this->session->set('panier', $panier);
    }

    public function remove(int $id)
    {
        $panier = $this->session->get('panier', []);

        if ($panier[$id] > 1) {
            $panier[$id]--;
        } else {
            unset($panier[$id]);
        }

        $this->session->set('panier', $panier);
    }

    public function getFullCart(): array
    {
        $panier = $this->session->get('panier', []);

        $panierWithData = [];

        foreach ($panier as $id => $quantity) {
            $panierWithData[] = [
                'forfait' => $this->SubscriptionRepository->find($id),
                'quantity' => $quantity
            ];
        }

        return $panierWithData;
    }

    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getFullCart() as $item) {
            $totalItem = $item['forfait']->getPrice() * $item['quantity'];
            $total += $totalItem;
        }
        return $total;
    }

    public function getForfaitTitle(): string
    {
        $title = "";

        foreach ($this->getFullCart() as $item) {
            $title .= $item['forfait']->getTitle() . " ";
        }

        return $title;
    }
}
