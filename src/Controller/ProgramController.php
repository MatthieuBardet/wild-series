<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Episode;
use App\Entity\Season;
use App\Form\ProgramType;
use App\Form\SearchProgramFormType;
use App\Repository\ProgramRepository;
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
use App\Form\CommentType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/programs", name="program_")
 */
class ProgramController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @param ProgramRepository $programRepository
     * @return Response
     */

    public function index(Request $request, ProgramRepository $programRepository): Response
    {
        $form = $this->createForm(SearchProgramFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $search = $form->getData()['search'];
            $programs = $programRepository->findLikeName($search);
        } else {
            $programs = $programRepository->findAll();
        }

        return $this->render('programs/index.html.twig', [
            'programs' => $programs,
            'form' => $form->createView(),
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
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer): Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $program->setSlug($slugify->generate($program->getTitle()));
            $entityManager = $this->getDoctrine()->getManager();
            $program->setOwner($this->getUser());
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
                'No program with id : ' . $program . ' found in program\'s table.'
            );
        }

        return $this->render("programs/show.html.twig", ['program' => $program]);
    }

    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET","POST"})
     * @param Slugify $slugify
     * @param Program $program
     * @param Request $request
     * @return Response
     */
    public function edit(Slugify $slugify, Program $program, Request $request): Response
    {
        // Check wether the logged in user is the owner of the program
        if (!($this->getUser() == $program->getOwner())) {
            // If not the owner, throws a 403 Access Denied exception
            throw new AccessDeniedException('Only the owner can edit the program!');
        }
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $program->setSlug($slugify->generate($program->getTitle()));
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('programs/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
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
     * @param Request $request
     * @return Response
     */

    public function showEpisode(Program $program, Season $season, Episode $episode, Request $request): Response
    {
        $comments = new Comment();
        $form = $this->CreateForm(CommentType::class, $comments);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comments->setUser($this->getUser());
            $comments->setEpisode($episode);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comments);
            $entityManager->flush();
            return $this->redirectToRoute('program_episode_show', [
                'program' => $program->getSlug(),
                'season' => $season->getId(),
                'episode' => $episode->getSlug(),
            ]);
        }

        return $this->render("programs/episode_show.html.twig", [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{program}/seasons/{season}/episode/{episode}/comment/{comment<^[0-9]+$>}",
     *     methods={"DELETE"}, name="comment_delete")
     * @param Request $request
     * @param Comment $comment
     * @param Program $program
     * @param Season $season
     * @param Episode $episode
     * @return Response
     */

        public function deleteComment(Request $request, Comment $comment,  Program $program, Season $season, Episode $episode): Response
        {
            if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($comment);
                $entityManager->flush();
            }

            return $this->redirectToRoute('program_episode_show', [
                'program' => $program->getSlug(),
                'season' => $season->getId(),
                'episode' => $episode->getSlug()
            ]);
        }
}