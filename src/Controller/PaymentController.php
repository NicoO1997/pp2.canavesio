<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;

class PaymentController extends AbstractController
{
    /**
     * Autentica con Mercado Pago utilizando el access token.
     */
    protected function authenticate(): void
    {
        // Obtener el access token desde el archivo .env
        $mpAccessToken = $this->getParameter('mercado_pago_access_token');

        // Configurar el SDK de Mercado Pago con el access token
        MercadoPagoConfig::setAccessToken($mpAccessToken);

        // (Opcional) Configurar el entorno de ejecución en LOCAL si estás probando en localhost
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
    }

    /**
     * Crea una preferencia de pago para Checkout Pro.
     */
    #[Route('/create-preference', name: 'create_preference')]
    public function createPreference(): Response
    {
        // Autenticar con Mercado Pago
        $this->authenticate();

        // Crear los productos que se van a comprar
        $item1 = [
            "id" => "1234567890",
            "title" => "Producto 1",
            "description" => "Descripción del Producto 1",
            "quantity" => 1,
            "currency_id" => "ARS",
            "unit_price" => 100.00
        ];

        $items = [$item1];

        // Configurar la información del pagador
        $payer = [
            "name" => "Nombre",
            "surname" => "Apellido",
            "email" => "user@test.com",
        ];

        // Crear la solicitud de preferencia
        $request = $this->createPreferenceRequest($items, $payer);

        // Instanciar el cliente de preferencia
        $client = new PreferenceClient();

        try {
            // Crear la preferencia en Mercado Pago
            $preference = $client->create($request);
            $preferenceId = $preference->id; // Obtener el ID de la preferencia

            // Renderizar la vista Twig y pasar el ID de la preferencia
            return $this->render('payment.html.twig', [
                'preferenceId' => $preferenceId
            ]);

        } catch (MPApiException $e) {
            return new Response("Error al crear la preferencia: " . $e->getMessage(), 500);
        } catch (\Exception $e) {
            return new Response("Error: " . $e->getMessage(), 500);
        }
    }

    /**
     * Crear el objeto de solicitud de preferencia para enviar a la API de Mercado Pago.
     */
    private function createPreferenceRequest(array $items, array $payer): array
    {
        $paymentMethods = [
            "excluded_payment_methods" => [],
            "installments" => 12,
            "default_installments" => 1
        ];

        $backUrls = [
            'success' => $this->generateUrl('payment_success', [], true),
            'failure' => $this->generateUrl('payment_failure', [], true)
        ];

        return [
            "items" => $items,
            "payer" => $payer,
            "payment_methods" => $paymentMethods,
            "back_urls" => $backUrls,
            "statement_descriptor" => "NAME_DISPLAYED_IN_USER_BILLING",
            "external_reference" => "1234567890",
            "expires" => false,
            "auto_return" => 'approved',
        ];
    }

    #[Route('/payment-success', name: 'payment_success')]
    public function paymentSuccess(): Response
    {
        return new Response("Pago realizado con éxito.");
    }

    #[Route('/payment-failure', name: 'payment_failure')]
    public function paymentFailure(): Response
    {
        return new Response("El pago ha fallado.");
    }
}
