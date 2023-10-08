<?php

namespace App\Tests\Unit;

use App\DependencyInjection\VacancyHandler;
use App\Entity\Vacancy;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityManager;

class VacancyHandlerTest extends KernelTestCase
{
    private ?EntityManager $entityManager;
    private VacancyHandler $vacancyHandler;
    private DateTimeImmutable $currentDate;
    private DateTimeImmutable $date;
    private int $price;
    private int $free;

    protected function setUp(): void
    {
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->vacancyHandler = self::getContainer()->get(VacancyHandler::class);

        $this->currentDate = new DateTimeImmutable();

        $this->date = $this->currentDate->modify('+1 day');
        $this->price = rand(100, 10000);
        $this->free = rand(1, 50);

        $this->entityManager->getConnection()->setNestTransactionsWithSavepoints(true);
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testSuccessfulUseOfCreateMethod(): void
    {
        $data = $this->prepareVacancyBasicPayload();

        /**
         * @var Vacancy $vacancy
         */
        $vacancy = $this->vacancyHandler->create($data);

        $this->assertNotNull($vacancy);
        $this->assertSame($this->price, $vacancy->getPrice());
        $this->assertSame($this->free, $vacancy->getFree());
        $this->assertSame($this->date->format('Y-m-d'), $vacancy->getFormatedDate());
    }

    public function testCreateMethodWithDateEqualNull(): void
    {
        $this->testCreateMethodWithFieldEqualNull('date');
    }

    public function testCreateMethodWithPriceEqualNull(): void
    {
        $this->testCreateMethodWithFieldEqualNull('price');
    }

    public function testCreateMethodWithFreeEqualNull(): void
    {
        $this->testCreateMethodWithFieldEqualNull('free');
    }

    private function testCreateMethodWithFieldEqualNull(string $field)
    {
        $data = $this->prepareVacancyBasicPayload();
        $data[$field] = null;

        /**
         * @var Vacancy $vacancy
         */
        $vacancy = $this->vacancyHandler->create($data);

        $this->assertSame(null, $vacancy);
    }

    private function prepareVacancyBasicPayload(): array
    {
        return [
            'date' => $this->date->format('Y-m-d'),
            'price' => $this->price,
            'free' => $this->free,
        ];
    }
}
