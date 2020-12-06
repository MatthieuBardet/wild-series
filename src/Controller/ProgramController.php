<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;

/**
 * @Route("/programs", name="program_")
 */

class ProgramController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */

    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();
        return $this->render('programs/index.html.twig', [
            'programs' => $programs,
        ]);
    }

    /**
     * Getting a program by id
     *
     * @Route("/show/{id<^[0-9]+$>}", name="show")
     * @return Response
     */
    public function show(int $id):Response
    {
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['id' => $id]);

        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : '.$id.' found in program\'s table.'
            );
        }
        return $this->render('programs/show.html.twig', [
            'program' => $program,
        ]);
    }

    /**
     * @Route ("/{programId}/season/{seasonId}", name="season_show")
     * @param int $programId
     * @param int $seasonId
     * @return Response
     */

    public function showSeason(int $programId, int $seasonId)
    {
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['id' => $programId]);

        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findOneBy(['program_id' => $programId,'id' => $seasonId]);

        $episodes = $this->getDoctrine()
            ->getRepository(Episode::class)
            ->findBy(['season_id' => $seasonId]);

        return $this->render("programs/season_show.html.twig", [
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes
        ]);
    }
}