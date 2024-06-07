<?php

namespace App\Controller\API;

use App\Entity\Localidad;
use App\Entity\Provincia;
use App\Entity\Usuario;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Vivienda;
use App\Repository\ViviendaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ApiViviendaController extends AbstractController
{

    #[Route("/vivienda/crear", name: "postVivienda", methods: ["POST"])]
    public function createVivienda(Request $request, EntityManagerInterface $em): JsonResponse
    {
        // Obtener los datos de la solicitud
        $data = json_decode($request->getContent(), true);
    
        // Extraer los datos de la solicitud
        $titulo = $data['titulo'] ?? null;
        $descripcion = $data['descripcion'] ?? null;
        $npersonas = $data['npersonas'] ?? null;
        $punto_inicio = $data['punto_inicio'] ?? null;
        $localidad_id = $data['localidad_id'] ?? null;
        $usuario_id = $data['usuario_id'] ?? null;
    
        // Verificar si se recibieron todos los campos necesarios
        if (!isset($titulo, $descripcion, $npersonas, $punto_inicio, $localidad_id)) {
            return $this->json(['message' => 'Faltan campos obligatorios'], 400);
        }
    
        // Cargar la instancia de Localidad correspondiente al ID proporcionado
        $localidad = $em->getRepository(Localidad::class)->find($localidad_id);
    
        // Verificar si se encontró la localidad
        if (!$localidad) {
            return $this->json(['message' => 'Localidad no encontrada'], 404);
        }
    
        // Crear una nueva instancia de Vivienda y asignar los datos
        $vivienda = new Vivienda();
        $vivienda->setTitulo($titulo);
        $vivienda->setDescripcion($descripcion);
        $vivienda->setNpersonas($npersonas);
        $vivienda->setLocalidad($localidad);
        // Asignar el usuario si es necesario
        if ($usuario_id) {
            // Cargar la instancia de Usuario correspondiente al ID proporcionado
            $usuario = $em->getRepository(Usuario::class)->find($usuario_id);
            // Verificar si se encontró el usuario
            if (!$usuario) {
                return $this->json(['message' => 'Usuario no encontrado'], 404);
            }
            $vivienda->setUsuario($usuario);
        }
        $vivienda->setLocalizacion($punto_inicio);
    
        // Persistir la vivienda en la base de datos
        $em->persist($vivienda);
        $em->flush();
    
        // Obtener el ID de la vivienda creada
        $viviendaId = $vivienda->getId();
    
        // Devolver una respuesta con el ID de la vivienda creada
        return $this->json(['message' => $viviendaId], 201);
    }
    


    

    #[Route('/api/viviendas', name: 'api_viviendas', methods: ['GET'])]
    public function getViviendas(ViviendaRepository $viviendaRepository): JsonResponse
    {
        $viviendas = $viviendaRepository->findAll();
    
        $data = [];
        foreach ($viviendas as $vivienda) {
            $localidad = $vivienda->getLocalidad();
            $provincia = $localidad ? $localidad->getProvincia() : null;
    
            $categorias = [];
            foreach ($vivienda->getCategoria() as $categoria) {
                $categorias[] = [
                    'id' => $categoria->getId(),
                    'nombre' => $categoria->getNombre(),
                ];
            }
    
            $viviendaFotos = [];
            foreach ($vivienda->getViviendaFotos() as $foto) {
                $viviendaFotos[] = [
                    'id' => $foto->getId(),
                    'foto_url' => $foto->getFotoUrl(),
                ];
            }
    
            $data[] = [
                'id' => $vivienda->getId(),
                'titulo' => $vivienda->getTITULO(),
                'descripcion' => $vivienda->getDESCRIPCION(),
                'npersonas' => $vivienda->getnpersonas(),
                'localidad' => [
                    'id' => $localidad ? $localidad->getId() : null,
                    'nombre' => $localidad ? $localidad->getNOMBRE() : null,
                ],
                'provincia' => [
                    'id' => $provincia ? $provincia->getId() : null,
                    'nombre' => $provincia ? $provincia->getNOMBRE() : null,
                ],
                'categorias' => $categorias,
                'vivienda_fotos' => $viviendaFotos,
            ];
        }
    
        return new JsonResponse($data);
    }


    #[Route('/api/viviendas/{id}', name: 'api_vivienda_by_id', methods: ['GET'])]
    public function getViviendaById(ViviendaRepository $viviendaRepository, $id): JsonResponse
    {
        // Busca la vivienda por su ID en el repositorio
        $vivienda = $viviendaRepository->find($id);

        // Verifica si la vivienda existe
        if (!$vivienda) {
            // Retorna una respuesta JSON con un mensaje de error si no se encuentra la vivienda
            return new JsonResponse(['message' => 'No se encontró la vivienda con el ID proporcionado'], Response::HTTP_NOT_FOUND);
        }

        // Prepara los datos de la vivienda para la respuesta JSON
        $localidad = $vivienda->getLocalidad();
        $provincia = $localidad ? $localidad->getProvincia() : null;

        $categorias = [];
        foreach ($vivienda->getCategoria() as $categoria) {
            $categorias[] = [
                'id' => $categoria->getId(),
                'nombre' => $categoria->getNombre(),
            ];
        }

        $viviendaFotos = [];
        foreach ($vivienda->getViviendaFotos() as $foto) {
            $viviendaFotos[] = [
                'id' => $foto->getId(),
                'foto_url' => $foto->getFotoUrl(),
            ];
        }

        // Construye la respuesta JSON con los datos de la vivienda
        $data = [
            'id' => $vivienda->getId(),
            'titulo' => $vivienda->getTITULO(),
            'descripcion' => $vivienda->getDESCRIPCION(),
            'npersonas' => $vivienda->getnpersonas(),
            'localidad' => [
                'id' => $localidad ? $localidad->getId() : null,
                'nombre' => $localidad ? $localidad->getNOMBRE() : null,
            ],
            'provincia' => [
                'id' => $provincia ? $provincia->getId() : null,
                'nombre' => $provincia ? $provincia->getNOMBRE() : null,
            ],
            'categorias' => $categorias,
            'vivienda_fotos' => $viviendaFotos,
        ];

        // Retorna la respuesta JSON con los datos de la vivienda
        return new JsonResponse($data);
    }
    
}