<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * End-to-end coverage of both endpoints against the seeded database, so the
 * routing, the payload mapping, the validation and the error shape are all
 * exercised the way a client meets them.
 *
 * Requires the test database to be prepared; `make test` does that first.
 */
final class PricingControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('pricedRequests')]
    public function testCalculatesThePrice(array $payload, float $expectedPrice): void
    {
        $this->client->jsonRequest('POST', '/calculate-price', $payload);

        self::assertResponseIsSuccessful();
        self::assertSame($expectedPrice, $this->responseBody()['price']);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, float}>
     */
    public static function pricedRequests(): iterable
    {
        yield 'Iphone for a Greek customer' => [
            ['product' => 1, 'taxNumber' => 'GR123456789'],
            124.0,
        ];

        yield 'Iphone for a Greek customer with a 6% coupon' => [
            ['product' => 1, 'taxNumber' => 'GR123456789', 'couponCode' => 'P6'],
            116.56,
        ];

        yield 'Iphone for a German customer with a 15 EUR coupon' => [
            ['product' => 1, 'taxNumber' => 'DE123456789', 'couponCode' => 'D15'],
            101.15,
        ];

        yield 'Iphone for a German customer with a 7.5% coupon' => [
            ['product' => 1, 'taxNumber' => 'DE123456789', 'couponCode' => 'P7H'],
            110.08,
        ];

        yield 'headphones for an Italian customer' => [
            ['product' => 2, 'taxNumber' => 'IT12345678900'],
            24.4,
        ];

        yield 'case for a French customer' => [
            ['product' => 3, 'taxNumber' => 'FRAB123456789'],
            12.0,
        ];
    }

    public function testReportsTheBreakdownAlongsideThePrice(): void
    {
        $this->client->jsonRequest('POST', '/calculate-price', [
            'product' => 1,
            'taxNumber' => 'GR123456789',
            'couponCode' => 'P6',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame([
            'product' => 'Iphone',
            'price' => 116.56,
            'currency' => 'EUR',
            'breakdown' => [
                'subtotal' => 100.0,
                'discount' => 6.0,
                'taxRate' => 24.0,
                'tax' => 22.56,
            ],
        ], $this->responseBody());
    }

    /**
     * @param array<string, mixed> $payload
     */
    #[DataProvider('rejectedRequests')]
    public function testRejectsInvalidInput(array $payload, string $expectedField): void
    {
        $this->client->jsonRequest('POST', '/calculate-price', $payload);

        self::assertResponseStatusCodeSame(422);
        self::assertSame([$expectedField], $this->invalidFields());
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function rejectedRequests(): iterable
    {
        yield 'unknown product' => [['product' => 999, 'taxNumber' => 'DE123456789'], 'product'];
        yield 'product that is not a number' => [['product' => 'abc', 'taxNumber' => 'DE123456789'], 'product'];
        yield 'product id of zero' => [['product' => 0, 'taxNumber' => 'DE123456789'], 'product'];
        yield 'unsupported country' => [['product' => 1, 'taxNumber' => 'XX123456789'], 'taxNumber'];
        yield 'tax number of the wrong length' => [['product' => 1, 'taxNumber' => 'DE12345678'], 'taxNumber'];
        yield 'coupon the seller never issued' => [
            ['product' => 1, 'taxNumber' => 'DE123456789', 'couponCode' => 'P50'],
            'couponCode',
        ];
    }

    /**
     * A non-positive id used to produce two violations on one field, because
     * every constraint ran. They are sequenced now, so it produces one.
     */
    public function testReportsANonPositiveProductIdOnlyOnce(): void
    {
        $this->client->jsonRequest('POST', '/calculate-price', ['product' => 0, 'taxNumber' => 'DE123456789']);

        self::assertResponseStatusCodeSame(422);
        self::assertCount(1, $this->responseBody()['errors']);
    }

    public function testReportsEveryMissingFieldAtOnce(): void
    {
        $this->client->jsonRequest('POST', '/calculate-price', []);

        self::assertResponseStatusCodeSame(422);
        self::assertSame(['product', 'taxNumber'], $this->invalidFields());
    }

    public function testRejectsABodyThatIsNotJson(): void
    {
        $this->client->request(
            'POST',
            '/calculate-price',
            server: ['CONTENT_TYPE' => 'application/json'],
            content: 'not json',
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testAcceptsOnlyPostRequests(): void
    {
        $this->client->request('GET', '/calculate-price');

        self::assertResponseStatusCodeSame(405);
    }

    public function testCompletesAPurchase(): void
    {
        $this->client->jsonRequest('POST', '/purchase', [
            'product' => 1,
            'taxNumber' => 'IT12345678900',
            'couponCode' => 'D15',
            'paymentProcessor' => 'paypal',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSame('paid', $this->responseBody()['status']);
        self::assertSame(103.7, $this->responseBody()['price']);
    }

    public function testReportsAPaymentTheProcessorRefuses(): void
    {
        // 10 EUR plus German VAT stays under the 100 EUR Stripe requires.
        $this->client->jsonRequest('POST', '/purchase', [
            'product' => 3,
            'taxNumber' => 'DE123456789',
            'paymentProcessor' => 'stripe',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSame(['paymentProcessor'], $this->invalidFields());
    }

    public function testRejectsAnUnknownPaymentProcessor(): void
    {
        $this->client->jsonRequest('POST', '/purchase', [
            'product' => 1,
            'taxNumber' => 'DE123456789',
            'paymentProcessor' => 'bitcoin',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSame(['paymentProcessor'], $this->invalidFields());
    }

    public function testRequiresAPaymentProcessorToPurchase(): void
    {
        $this->client->jsonRequest('POST', '/purchase', [
            'product' => 1,
            'taxNumber' => 'DE123456789',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertSame(['paymentProcessor'], $this->invalidFields());
    }

    /**
     * @return array<string, mixed>
     */
    private function responseBody(): array
    {
        return json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @return list<string> the fields the API complained about, in order
     */
    private function invalidFields(): array
    {
        return array_column($this->responseBody()['errors'] ?? [], 'field');
    }
}
