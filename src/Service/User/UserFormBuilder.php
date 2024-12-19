<?php

namespace App\Service\User;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UpdateUserFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserFormBuilder
{


    public function __construct(private readonly FormFactoryInterface $formFactory)
    {

    }


    public function buildRegistrationForm() : FormInterface
    {
        return $this->formFactory->create(RegistrationFormType::class, new User());
    }

    public function buildUpdateForm(UserInterface $user) : FormInterface
    {
        return $this->formFactory->create(UpdateUserFormType::class, $user);
    }
}