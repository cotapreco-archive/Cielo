<?php

namespace CotaPreco\Cielo;

use PHPUnit_Framework_TestCase as TestCase;

/**
 * @author Andrey K. Vital <andreykvital@gmail.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransactionRequestTest extends TestCase
{
    /**
     * @var TransactionRequest
     */
    private $request;

    /**
     * @return array
     */
    public function provideRequests()
    {
        $merchant = Merchant::fromAffiliationIdAndKey(
            '1006993069',
            '25fbb99741c739dd84d7b06ec78c9bac718838630f30b112d033ce2e621b34f3'
        );

        $card = CreditCard::createWithSecurityCode(
            '4551870000000183',
            CreditCardExpiration::fromYearAndMonth(2018, 5),
            Cvv::fromString('123')
        );

        $paymentMethod = PaymentMethod::forIssuerAsOneTimePayment(CardIssuer::fromCreditCardType(CreditCardType::VISA));

        $order = new Order('123452', 1000);

        return [
            [
                TransactionAuthorizationIndicator::ONLY_AUTHENTICATE,
                TransactionRequest::createAndAuthenticateOnly(
                    $merchant,
                    CardHolder::fromCard($card),
                    $order,
                    $paymentMethod,
                    true
                )
            ],
            [
                TransactionAuthorizationIndicator::AUTHORIZE,
                TransactionRequest::createAndAuthorizeOnly(
                    $merchant,
                    CardHolder::fromCard($card),
                    $order,
                    $paymentMethod,
                    true,
                    'http://localhost/cielo.php'
                )
            ],
            [
                TransactionAuthorizationIndicator::AUTHORIZE_ONLY_IF_AUTHENTICATED,
                TransactionRequest::createAndAuthorizeOnlyIfAuthenticated(
                    $merchant,
                    CardHolder::fromCard($card),
                    $order,
                    $paymentMethod,
                    true
                )
            ],
            [
                TransactionAuthorizationIndicator::AUTHORIZE_WITHOUT_AUTHENTICATION,
                TransactionRequest::createAndAuthorizeWithoutAuthentication(
                    $merchant,
                    CardHolder::fromCard($card),
                    $order,
                    $paymentMethod,
                    true
                )
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        list(, $request) = array_shift($this->provideRequests());

        $this->request = $request;
    }

    /**
     * @test
     */
    public function getMerchant()
    {
        $this->assertInstanceOf(Merchant::class, $this->request->getMerchant());
    }

    /**
     * @test
     */
    public function getHolder()
    {
        $this->assertInstanceOf(IdentifiesHolder::class, $this->request->getHolder());
    }

    /**
     * @test
     */
    public function getOrder()
    {
        $this->assertInstanceOf(Order::class, $this->request->getOrder());
    }

    /**
     * @test
     */
    public function getPaymentMethod()
    {
        $this->assertInstanceOf(PaymentMethod::class, $this->request->getPaymentMethod());
    }

    /**
     * @test
     */
    public function getReturnUrl()
    {
        $this->assertNull($this->request->getReturnUrl());
    }

    /**
     * @test
     */
    public function getBin()
    {
        $this->assertInstanceOf(Bin::class, $this->request->getBin());
    }

    /**
     * @test
     */
    public function shouldCapture()
    {
        $this->assertTrue($this->request->shouldCapture());
    }

    /**
     * @test
     */
    public function shouldGenerateToken()
    {
        $this->assertFalse($this->request->shouldGenerateToken());
    }

    /**
     * @test
     * @param int $indicator
     * @param TransactionRequest $request
     * @dataProvider provideRequests
     */
    public function getAuthorizeIndicator($indicator, $request)
    {
        $this->assertEquals($indicator, $request->getAuthorizeIndicator());
    }
}