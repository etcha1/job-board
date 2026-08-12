<?php

namespace App\Controller;

use App\Entity\JobOffer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class JobOfferController extends AbstractController
{
	#[Route('/job-offers/{id}', name: 'job_offer_show')]
	public function show(EntityManagerInterface $entityManager, int $id): Response
	{
		$jobOffer = $entityManager
			->getRepository(JobOffer::class)
			->find($id);

		if (!$jobOffer) {
			throw $this->createNotFoundException('Job offer not found');
		}

		return $this->render('job_offer/show.html.twig', [
			'jobOffer' => $jobOffer,
		]);
	}
}
