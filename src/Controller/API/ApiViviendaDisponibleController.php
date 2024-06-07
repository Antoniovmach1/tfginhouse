<?php

namespace App\Controller\API;

use App\Entity\DisponibilidadVivienda;
use App\Entity\Vivienda;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiViviendaDisponibleController extends AbstractController
{
    #[Route('/viviendadisponibilidad/crear', name: 'postTour', methods: ['POST'])]
    public function createTour(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $viviendaId = $data['vivienda_id'];
        $tours = $data['jsonArrayProgramacion'];

        // Validar vivienda_id
        if (!$viviendaId) {
            return $this->json(['message' => 'Falta el ID de la vivienda'], 400);
        }

        $vivienda = $em->getRepository(Vivienda::class)->find($viviendaId);
        if (!$vivienda) {
            return $this->json(['message' => 'Vivienda no encontrada'], 404);
        }

        // Mapa de días de la semana
        $dayMap = [
            'L' => 'Mon',
            'M' => 'Tue',
            'X' => 'Wed',
            'J' => 'Thu',
            'V' => 'Fri',
            'S' => 'Sat',
            'D' => 'Sun',
        ];

        foreach ($tours as $tourData) {
            $fecha_inicio = \DateTime::createFromFormat('d/m/Y', $tourData['temporadaIni']);
            $fecha_fin = \DateTime::createFromFormat('d/m/Y', $tourData['temporadaFin']);
            $dias = array_map(function ($dia) use ($dayMap) {
                return $dayMap[$dia];
            }, explode(',', $tourData['diasSemana']));

            $interval = new \DateInterval('P1D');
            $period = new \DatePeriod($fecha_inicio, $interval, $fecha_fin->modify('+1 day'));

            foreach ($period as $date) {
                $dayOfWeek = $date->format('D');
                if (in_array($dayOfWeek, $dias)) {
                    $disponibilidad = new DisponibilidadVivienda();
                    $disponibilidad->setFecha($date);
                    $disponibilidad->setPrecio($tourData['precio']);
                    $disponibilidad->setVivienda($vivienda);

                    $em->persist($disponibilidad);
                }
            }
        }

        $em->flush();

        return $this->json(['message' => 'Disponibilidad creada con éxito'], 201);
    }
}
