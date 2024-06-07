<?php

namespace App\Repository;

use App\Entity\Vivienda;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vivienda>
 *
 * @method Vivienda|null find($id, $lockMode = null, $lockVersion = null)
 * @method Vivienda|null findOneBy(array $criteria, array $orderBy = null)
 * @method Vivienda[]    findAll()
 * @method Vivienda[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ViviendaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vivienda::class);
    }

    public function findByFilters(?int $localidadId, ?int $provinciaId): array
    {
        $qb = $this->createQueryBuilder('v')
            ->leftJoin('v.localidad', 'l')
            ->leftJoin('l.provincia', 'p');
    
        if ($localidadId !== null) {
            $qb->andWhere('l.id = :localidadId')
               ->setParameter('localidadId', $localidadId);
        }
    
        if ($provinciaId !== null) {
            $qb->andWhere('p.id = :provinciaId')
               ->setParameter('provinciaId', $provinciaId);
        }
    
        return $qb->getQuery()->getResult();
    }
}
