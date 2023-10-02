<?php

namespace App\Requests;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReservationRequest extends BaseRequest
{
    #[Type('integer')]
    #[NotBlank()]
    protected $id;

    #[Type('date')]
    #[NotBlank([])]
    protected $startDate;

    #[Type('date')]
    #[NotBlank([])]
    protected $endDate;

    #[Type('integer')]
    #[NotBlank([])]
    protected $bookedPlaces;
}

