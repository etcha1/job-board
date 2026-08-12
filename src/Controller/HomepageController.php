<?php

namespace App\Controller;

use App\Entity\JobOffer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
	#[Route('/', name: 'homepage')]
	public function index(EntityManagerInterface $entityManager): Response
	{
		$lastJobOffers = $entityManager
			->getRepository(JobOffer::class)
			->findBy([], ['postedAt' => 'DESC'], 5);

		return $this->render('homepage/landing.html.twig', [
			'title' => 'Job Board',
			'lastJobOffers' => $lastJobOffers,
		]);
	}
}
