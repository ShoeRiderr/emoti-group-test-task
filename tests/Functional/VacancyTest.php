<?php

namespace App\Tests\Functional;

use App\DataFixtures\VacancyFixtures;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Response;

class VacancyTest extends ApiTestCase
{
    private $currentDate;
    private $tomorrow;
    private $dayAfterTomorrow;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currentDate = new DateTimeImmutable();
        $this->tomorrow = $this->currentDate->modify('+1 day');
        $this->dayAfterTomorrow = $this->currentDate->modify('+2 day');

        $this->databaseTool->loadFixtures([
            VacancyFixtures::class
        ]);
    }

    public function testSuccessfullCollectionFilter(): void
    {
        $this->client->request('GET', '/api/vacancies', [
            'query' => [
                'startDate' => $this->tomorrow->format('Y-m-d'),
                'endDate' => $this->dayAfterTomorrow->format('Y-m-d'),
                'free' => 2,
            ],
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            '@context'         => '/api/contexts/Vacancy',
            '@id'              => '/api/vacancies',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 2,
        ]);
    }

    public function testVacancyGetCollectionMethodWithEmptyStartDateField(): void
    {
        $this->client->request('GET', '/api/vacancies', [
            'query' => [
                'endDate' => $this->dayAfterTomorrow->format('Y-m-d'),
                'free' => 2,
            ],
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            '@context'         => '/api/contexts/Vacancy',
            '@id'              => '/api/vacancies',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 2,
        ]);
    }

    public function testVacancyGetCollectionMethodWithEmptyEndDateField(): void
    {
        $this->client->request('GET', '/api/vacancies', [
            'query' => [
                'startDate' => $this->dayAfterTomorrow->format('Y-m-d'),
                'free' => 2,
            ],
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            '@context'         => '/api/contexts/Vacancy',
            '@id'              => '/api/vacancies',
            '@type'            => 'hydra:Collection',
        ]);
    }

    public function testVacancyGetCollectionMethodWithEmptyFreeField(): void
    {
        $this->client->request('GET', '/api/vacancies', [
            'query' => [
                'startDate' => $this->tomorrow->format('Y-m-d'),
                'endDate' => $this->dayAfterTomorrow->format('Y-m-d'),
            ],
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame(
            'content-type',
            'application/ld+json; charset=utf-8'
        );

        $this->assertJsonContains([
            '@context'         => '/api/contexts/Vacancy',
            '@id'              => '/api/vacancies',
            '@type'            => 'hydra:Collection',
            'hydra:totalItems' => 2,
        ]);
    }

    public function testVacancyGetCollectionMethodWithInvalidStartDateField(): void
    {
        $this->client->request('GET', '/api/vacancies', [
            'query' => [
                'startDate' => 'test',
                'endDate' => $this->dayAfterTomorrow->format('Y-m-d'),
                'free' => 2,
            ],
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testVacancyGetCollectionMethodWithInvalidEndDateField(): void
    {
        $this->client->request('GET', '/api/vacancies', [
            'query' => [
                'startDate' => $this->dayAfterTomorrow->format('Y-m-d'),
                'endDate' => 'test',
                'free' => 2,
            ],
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testVacancyGetCollectionMethodWithInvalidFreeField(): void
    {
        $this->client->request('GET', '/api/vacancies', [
            'query' => [
                'startDate' => $this->tomorrow->format('Y-m-d'),
                'endDate' => $this->dayAfterTomorrow->format('Y-m-d'),
                'free' => 'test'
            ],
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
