<?php

namespace App\Controller\API;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;
use Symfony\Component\HttpFoundation\Response;

class ApiUsuarioController extends AbstractController
{
    #[Route('/api/usuario/comprar-premium', name: 'api_comprar_premium', methods: ['POST'])]
    public function comprarPremium(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $data['userId'] ?? null;

        if (!$userId) {
            return new JsonResponse(['error' => 'ID de usuario no proporcionado'], Response::HTTP_BAD_REQUEST);
        }

        $usuario = $entityManager->getRepository(Usuario::class)->find($userId);
        if (!$usuario) {
            return new JsonResponse(['error' => 'Usuario no encontrado'], Response::HTTP_NOT_FOUND);
        }

        $currentDate = new DateTime();

        // Verificar si la fecha premium del usuario no ha vencido
        if ($usuario->getPremium() && $usuario->getPremium() > $currentDate) {
            return new JsonResponse(['error' => 'El usuario ya es premium hasta ' . $usuario->getPremium()->format('Y-m-d')], Response::HTTP_BAD_REQUEST);
        }

        $year = (int) $currentDate->format('Y') + 1;
        $month = (int) $currentDate->format('m');
        $day = (int) $currentDate->format('d');

        if ($month === 2 && $day === 29) {
            $month = 3;
            $day = 1;
        }

        $premiumDate = new DateTime("$year-$month-$day");
        $usuario->setPremium($premiumDate);

        // Sumar 100 puntos al usuario
        $currentPoints = $usuario->getPuntos() ?? 0;
        $usuario->setPuntos($currentPoints + 100);

        $entityManager->persist($usuario);
        $entityManager->flush();

        return new JsonResponse([
            'success' => 'Usuario ahora es premium hasta ' . $premiumDate->format('Y-m-d'),
            'newPoints' => $usuario->getPuntos()
        ]);
    }
}