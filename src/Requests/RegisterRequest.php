<?php

namespace App\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterRequest
{
    public function __construct($email, $name, $password, $passwordConfirmation)
    {
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;
        $this->passwordConfirmation = $passwordConfirmation;
    }

    #[Assert\NotBlank]
    #[Assert\Email]
    public $email;

    #[Assert\NotBlank]
    public $name;

    #[Assert\NotBlank]
    public $password;

    #[Assert\NotBlank]
    #[Assert\IdenticalTo('password')]
    public $passwordConfirmation;
}
