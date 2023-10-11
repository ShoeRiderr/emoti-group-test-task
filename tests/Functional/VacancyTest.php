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

    public function testSuccessfulColectionWithoutFilter(): void
    {
        $this->client->request('GET', '/api/vacancies', [
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
            'hydra:totalItems' => VacancyFixtures::VACANCY_NUMBER,
        ]);
    }

    public function testSuccessfulCollectionFilter(): void
    {
        $response = $this->client->request('GET', '/api/vacancies', [
            'query' => [
                'startDate' => $this->tomorrow->format('Y-m-d'),
                'endDate' => $this->dayAfterTomorrow->format('Y-m-d'),
                'free' => 2,
            ],
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

        $responseContent = $response->getContent();
        $responseContent = json_decode($responseContent);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame(
            'content-type',
            'application/json'
        );

        $this->assertEquals(2, count($responseContent));
    }

    public function testVacancyGetCollectionMethodWithEmptyStartDateField(): void
    {
        $response = $this->client->request('GET', '/api/vacancies', [
            'query' => [
                'endDate' => $this->dayAfterTomorrow->format('Y-m-d'),
                'free' => 2,
            ],
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

        $responseContent = $response->getContent();
        $responseContent = json_decode($responseContent);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame(
            'content-type',
            'application/json'
        );

        $this->assertEquals(2, count($responseContent));
    }

    public function testVacancyGetCollectionMethodWithEmptyEndDateField(): void
    {
        $response = $this->client->request('GET', '/api/vacancies', [
            'query' => [
                'startDate' => $this->dayAfterTomorrow->format('Y-m-d'),
                'free' => 2,
            ],
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

        $responseContent = $response->getContent();
        $responseContent = json_decode($responseContent);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame(
            'content-type',
            'application/json'
        );

        $this-> assertTrue(empty($responseContent));
    }

    public function testVacancyGetCollectionMethodWithEmptyFreeField(): void
    {
        $response = $this->client->request('GET', '/api/vacancies', [
            'query' => [
                'startDate' => $this->tomorrow->format('Y-m-d'),
                'endDate' => $this->dayAfterTomorrow->format('Y-m-d'),
            ],
            'headers' => ['X-API-TOKEN' => self::API_TOKEN]
        ]);

        $responseContent = $response->getContent();
        $responseContent = json_decode($responseContent);

        $this->assertResponseIsSuccessful();

        $this->assertResponseHeaderSame(
            'content-type',
            'application/json'
        );

        $this->assertEquals(2, count($responseContent));
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
