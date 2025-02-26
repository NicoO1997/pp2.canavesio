<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use App\Repository\CartRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Psr\Log\LoggerInterface;

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

        // Configurar el entorno de ejecución para ambiente de producción
        // Ya que PRODUCTION no está definido, usamos el LOCAL como estaba originalmente
        // o simplemente comentamos esta línea si no es necesaria
        // MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
    }

    /**
     * Crea una preferencia de pago para Checkout Pro.
     */
    #[Route('/create-preference', name: 'create_preference')]
    public function createPreference(
        CartRepository $cartRepository, 
        Security $security,
        LoggerInterface $logger
    ): Response
    {
        
        $this->authenticate();
        
        $user = $security->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $cart = $cartRepository->findOneBy(['user' => $user]);
        if (!$cart || $cart->getCartProductOrders()->isEmpty()) {
            $this->addFlash('error', 'Tu carrito está vacío');
            return $this->redirectToRoute('cart_view');
        }

        // Crear el array de items para Mercado Pago
        $items = [];
        
        foreach ($cart->getCartProductOrders() as $cartProductOrder) {
            $product = $cartProductOrder->getProduct();
            $quantity = $cartProductOrder->getQuantity();
            $price = $product->getPrice();
            
            $items[] = [
                "id" => (string)$product->getId(),
                "title" => $product->getName(),
                "description" => $product->getDescription() ?? "Producto " . $product->getName(),
                "quantity" => $quantity,
                "currency_id" => "ARS", // Pesos argentinos
                "unit_price" => floatval($price)
            ];
        }

        // Configurar la información del pagador
        $payer = [
            "email" => $user->getUserIdentifier() ?? "Email"
        ];

        // Crear la solicitud de preferencia
        $request = $this->createPreferenceRequest($items, $payer, $user, $cart);

        // Instanciar el cliente de preferencia
        $client = new PreferenceClient();

        try {
            // Crear la preferencia en Mercado Pago
            $preference = $client->create($request);
            $preferenceId = $preference->id; // Obtener el ID de la preferencia

            // Registrar la creación exitosa de la preferencia
            $logger->info('Preferencia de Mercado Pago creada con éxito', [
                'preference_id' => $preferenceId
            ]);

            // Renderizar la vista Twig y pasar el ID de la preferencia
            return $this->render('payment.html.twig', [
                'preferenceId' => $preferenceId
            ]);

        } catch (MPApiException $e) {
            // Obtener los detalles del error de la API
            $response = $e->getApiResponse();
            $statusCode = $response->getStatusCode();
            
            // Corregimos el manejo del contenido de respuesta según la API
            // Obtenemos el contenido como string
            $responseContent = $response->getContent();
            
            // Luego lo convertimos a array si es posible
            $content = [];
            if (!empty($responseContent) && is_string($responseContent)) {
                $content = json_decode($responseContent, true) ?: [];
            }
            
            // Registrar el error detallado
            $logger->error('Error de Mercado Pago API', [
                'status_code' => $statusCode,
                'error_details' => $content
            ]);
            
            // Mostrar mensaje de error con detalles
            $errorMessage = isset($content['message']) ? $content['message'] : $e->getMessage();
            $this->addFlash('error', 'Error al procesar el pago: ' . $errorMessage);
            
            return $this->render('error/payment_error.html.twig', [
                'error' => 'Error al crear la preferencia de pago (Código: ' . $statusCode . '): ' . $errorMessage,
                'details' => $content
            ]);
        } catch (\Exception $e) {
            // Manejar cualquier otro tipo de excepción
            $logger->error('Error general en la creación de preferencia', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->addFlash('error', 'Error en el sistema de pagos. Por favor, intente más tarde.');
            
            return $this->render('error/payment_error.html.twig', [
                'error' => 'Error general: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Crear el objeto de solicitud de preferencia para enviar a la API de Mercado Pago.
     */
    private function createPreferenceRequest(array $items, array $payer, $user, $cart): array
    {
        $paymentMethods = [
            "excluded_payment_methods" => [],
            "installments" => 12,
            "default_installments" => 1
        ];

        $backUrls = [
            'success' => $this->generateUrl('payment_success', [], true),
            'failure' => $this->generateUrl('payment_failure', [], true),
            'pending' => $this->generateUrl('payment_pending', [], true)
        ];

        return [
            "items" => $items,
            "payer" => $payer,
            "payment_methods" => $paymentMethods,
            "back_urls" => $backUrls,
            "statement_descriptor" => "TU_NOMBRE_DE_TIENDA", // Reemplaza con el nombre real de tu tienda
            "external_reference" => "ORDEN-" . $user->getId() . "-" . time(),
            "expires" => false,
            "auto_return" => 'approved',
            "notification_url" => $this->generateUrl('mercado_pago_webhook', [], true),
            "metadata" => [
                "user_id" => $user->getId(),
                "cart_id" => $cart->getId()
            ]
        ];
    }

    #[Route('/payment-success', name: 'payment_success')]
    public function paymentSuccess(): Response
    {
        $this->addFlash('success', '¡Pago realizado con éxito!');
        return $this->render('payment/success.html.twig');
    }

    #[Route('/payment-failure', name: 'payment_failure')]
    public function paymentFailure(): Response
    {
        $this->addFlash('error', 'El pago ha fallado. Por favor, intente nuevamente.');
        return $this->render('payment/failure.html.twig');
    }

    #[Route('/payment-pending', name: 'payment_pending')]
    public function paymentPending(): Response
    {
        $this->addFlash('info', 'El pago está pendiente de confirmación.');
        return $this->render('payment/pending.html.twig');
    }

    #[Route('/mercado-pago-webhook', name: 'mercado_pago_webhook', methods: ['POST'])]
    public function handleWebhook(LoggerInterface $logger): Response
    {
        // Obtener los datos del webhook
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);
        
        // Registrar la notificación recibida
        $logger->info('Notificación de Mercado Pago recibida', [
            'data' => $data
        ]);
        
        // Aquí deberías implementar la lógica para procesar la notificación
        // Por ejemplo, actualizar el estado del pedido en tu base de datos
        
        // Responder con un 200 OK para confirmar recepción
        return new Response('OK', 200);
    }
}