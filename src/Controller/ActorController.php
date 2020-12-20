<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Repository\ActorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/actors")
 */
class ActorController extends AbstractController
{
    /**
     * @Route("/{id}", name="actors_show", methods={"GET"})
     * @param Actor $actors
     * @return Response
     */
    public function show(Actor $actors): Response
    {
        return $this->render('actors/show.html.twig', [
            'actors' => $actors,
        ]);
    }
}