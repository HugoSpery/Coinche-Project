<?php

namespace App\Service\User;

use App\Entity\User;
use App\Exception\InvalidCodeException;
use App\Exception\InvalidPasswordException;
use App\Exception\TooShortPasswordException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserFormHandler
{

    public function __construct(private UserManager $userManager,private Security $security,private UserPasswordHasherInterface $userPasswordHasher,private SluggerInterface $slugger,#[Autowire('%kernel.project_dir%/assets/images')] private string $avatarDirectory)
    {
    }


    public function handleRegistrationForm(Request $request,FormInterface $form)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()){
            throw new \Exception($form->getErrors(true)->current()->getMessage());
        }
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $user = $form->getData();
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setFake(false);
            $user->setAvatar('/images/avatar.png');
            $user->setTrophy(0);
            // encode the plain password
            if (strlen($plainPassword) < 6) {
                throw new TooShortPasswordException('Your password should be at least 6 characters');
            }
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z]).{6,}$/', $plainPassword)) {
                throw new InvalidPasswordException('Your password must contain at least one uppercase letter, and one lowercase letter');
            }
            $user->setPassword($this->userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRoles(['ROLE_SUPERUSER']);

            $this->userManager->save($user);

            // do anything else you need here, like send an email

            $this->security->login($user, 'form_login', 'user_firewall');
            return $user;
        }
        return null;
    }

    public function handleUpdateForm(Request $request,FormInterface $form)
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()){
            throw new \Exception($form->getErrors(true)->current()->getMessage());
        }
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $avatarFile = $form->get('avatar')->getData();
            if ($avatarFile){
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$avatarFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $avatarFile->move($this->avatarDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $user->setAvatar("/images/".$newFilename);

            }

            $this->userManager->save($user);
            return $user;
        }
        return null;
    }
}