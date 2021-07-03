<?php

namespace App\Service\Subscription;

use App\Repository\MusiqueRepository;

class SubscriptionService
{
	protected $musiqueRepository;

	public function __construct(MusiqueRepository $musiqueRepository)
	{
		$this->musiqueRepository = $musiqueRepository;
	}

	public function isValidSubscription(object $user, int $days = null, int $nbMusic)
	{
		$now = new \DateTime();
		$nbMusicsPerInterval = \count($this->musiqueRepository->findByUserInInterval($user, $user->getSubscriptionBeginAt(), $user->getSubscriptionEndAt()));

		$interval = $now->diff($user->getSubscriptionBeginAt(), true)->days;
		$response = (($interval > $days) || ($nbMusicsPerInterval >= $nbMusic)) ? false : $response = true;
		return $response;
	}

	public function isActiveSubscription(object $user, object $manager)
	{
		if (($user->getAmateurIsActive() || $user->getGoldenIsActive() || $user->getPremiumIsActive()) && ($user->getSubscriptionBeginAt() === null)) {
			$user->setSubscriptionBeginAt(new \DateTime());

			if ($user->getAmateurIsActive() && $user->getSubscriptionEndAt() === null) {
				$user->setSubscriptionEndAt(new \DateTime('+15 days'));
			}

			if ($user->getGoldenIsActive() && $user->getSubscriptionEndAt() === null) {
				$user->setSubscriptionEndAt(new \DateTime('+1 month'));
			}

			if ($user->getPremiumIsActive() && $user->getSubscriptionEndAt() === null) {
				$user->setSubscriptionEndAt(new \DateTime('+45 days'));
			}

			$manager->persist($user);
			$manager->flush();
		}
	}

	public function subscriptionChecker(object $user, bool $subs = null, int $days = null, int $nbMusic, object $manager)
	{
		if ($subs) {
			if (!$this->isValidSubscription($user, $days, $nbMusic)) {
				$user->setAmateurIsActive(false);
				$user->setGoldenIsActive(false);
				$user->setPremiumIsActive(false);
				$user->setSubscriptionBeginAt(null);
				$user->setSubscriptionEndAt(null);
				$manager->persist($user);
				$manager->flush();
			}
		}
	}
}
