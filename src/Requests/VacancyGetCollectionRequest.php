<?php

namespace App\Requests;

use Symfony\Component\Validator\Constraints as Assert;

class VacancyGetCollectionRequest
{
    public function __construct($startDate, $endDate, $free)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->free = $free;
    }

    #[Assert\Date]
    #[Assert\NotBlank(allowNull: true)]
    public $startDate;

    #[Assert\Date]
    #[Assert\NotBlank(allowNull: true)]
    public $endDate;

    #[Assert\Type('int')]
    #[Assert\NotBlank(allowNull: true)]
    public $free;
}
