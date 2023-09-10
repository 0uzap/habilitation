<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\UserType;
use App\Form\UserRoleType;

class BaseController extends AbstractController
{
    #[Route('/index', name: 'index')]
    public function index(): Response
    {
        return $this->render('base/index.html.twig', [
          
        ]);
    }

    #[Route('/moi', name: 'moi')]
    public function moi(Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        $user = $this->getuser();
        $form = $this->createForm(UserType::class, $user);
        $form->handlerequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface = $this->getDoctrine()->getManager();
            $entityManagerInterface->persist($user);
            $entityManagerInterface->flush();

            return $this->redirectToRoute('moi');
        }

        return $this->render('base/moi.html.twig', [
          'user' => $user,
          'form' => $form->createView(),
        ]);
    }

    #[Route('/private-panel', name: 'panel')]
    public function panel(): Response
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $users = $userRepository->findAll();

        $allRoles = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_MODERATOR'];    
        
        return $this->render('base/panel.html.twig', [
            'users' => $users,
            'allRoles' => $allRoles,
        ]);
    }

    #[Route('/verifier-utilisateur/{id}', name: 'verifier_utilisateur')]
    public function verifierUtilisateur($id, EntityManagerInterface $entityManagerInterface): Response
    {
       
        $user = $entityManagerInterface->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
      
        $user->setIsVerified(true);
        $entityManagerInterface->flush();

        return $this->redirectToRoute('panel');
    }

    #[Route('/gerer-roles/{id}', name: 'gerer_roles')]
    public function gererRoles($id, Request $request, EntityManagerInterface $entityManagerInterface): Response
    {
        $user = $entityManagerInterface->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }
    
        $form = $this->createForm(UserRoleType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManagerInterface->flush();

            return $this->redirectToRoute('panel');
        }

        return $this->render('base/gerer_roles.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/supprimer-utilisateur/{id}', name: 'supprimer_utilisateur')]
    public function supprimerUtilisateur($id, EntityManagerInterface $entityManagerInterface): Response
    {
        $user = $entityManagerInterface->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé');
        }

        $entityManagerInterface->remove($user);
        $entityManagerInterface->flush();

        return $this->redirectToRoute('panel');
    }
} 

