<?php

namespace App\Controller\Admin;

use App\Entity\Vivienda;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ViviendaCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Vivienda::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */

    #[Route('/creavivienda', name:"creavivienda")]
    public function creavivienda(): Response
    {



        return $this->render('creavivienda.html.twig', [
           
        ]);
       
    }

    #[Route('/viviendas', name:"viviendas")]
    public function viviendas(): Response
    {



        return $this->render('viviendas.html.twig', [
           
        ]);
       
    }


    #[Route('/vivienda/{id}', name: 'viviendas')]
    public function vivienda(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        // Obtén la vivienda correspondiente según el ID proporcionado
        $vivienda = $entityManager->getRepository(Vivienda::class)->find($id);

        // Verifica si la vivienda existe
        if (!$vivienda) {
            $this->addFlash('error', 'No se encontró la vivienda con el ID ' . $id);
            return $this->redirectToRoute('ruta_para_redireccionar');
        }

        // Renderiza la plantilla Twig y pasa la vivienda como variable
        return $this->render('viviendasindv.html.twig', [
            'vivienda' => $vivienda,
        ]);
    }



    #[Route('/ejemplo', name:"ejemplo")]
    public function ejemplo(): Response
    {



        return $this->render('ejemplo.html.twig', [
           
        ]);
       
    }
}
