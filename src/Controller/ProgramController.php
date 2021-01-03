<?php


namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Season;
use App\Form\ProgramType;
use App\Service\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use App\Entity\Program;

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
     *
     * @Route("/new", name="new")
     * @param Request $request
     * @param Slugify $slugify
     * @param MailerInterface $mailer
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer) : Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $program->setSlug($slugify->generate($program->getTitle()));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($program);
            $entityManager->flush();

            $email = (new Email())
                ->from('your_email@example.com')
                ->to('your_email@example.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('programs/newProgramEmail.html.twig', ['program' => $program]));

            $mailer->send($email);

            return $this->redirectToRoute('program_index');
        }
        return $this->render('programs/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }


    /**
     * @Route ("/{slug}", methods={"GET"}, name="show")
     * @param Program $program
     * @return Response
     */

    public function show(Program $program): Response
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : '.$program.' found in program\'s table.'
            );
        }

        return $this->render("programs/show.html.twig", ['program' => $program]);
    }

    /**
     * @Route ("/{program}/season/{season}", name="season_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"program": "slug"}})
     * @param Program $program
     * @param Season $season
     * @return Response
     */

    public function showSeason(Program $program, Season $season): Response
    {

        return $this->render("programs/season_show.html.twig", [
            'program' => $program,
            'season' => $season,
        ]);
    }

    /**
     * @Route ("/{program}/seasons/{season}/episode/{episode}", name="episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"program": "slug"}})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episode": "slug"}})
     * @param Program $program
     * @param Season $season
     * @param Episode $episode
     * @return Response
     */

    public function showEpisode(Program $program, Season $season, Episode $episode): Response
    {
        return $this->render("programs/episode_show.html.twig", [
            'program' => $program,
            'season' => $season,
            'episode' => $episode
        ]);
    }
}