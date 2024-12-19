<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\FriendRequestRepository;
use App\Repository\PartyRequestRepository;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Service\Menu;
use App\Service\User\UserFormBuilder;
use App\Service\User\UserFormHandler;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager,PaginatorInterface $paginator,UserRepository$userRepository,
                             UserFormBuilder $userFormBuilder,UserFormHandler $userFormHandler
    ): Response
    {
        try{
            $this->denyAccessUnlessGranted(UserVoter::ANONYMOUS);
        }catch (\Exception $e){
            return $this->redirectToRoute('app_home');
        }
        $form = $userFormBuilder->buildRegistrationForm();
        try {
            $user = $userFormHandler->handleRegistrationForm($request,$form);
            if ($user){
                $this->addFlash('success', 'You have successfully registered and connected!');
                return $this->redirectToRoute('app_home');
            }
        }catch (\Exception $e){
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
