<?php

namespace App\Controller\Admin;

use App\Entity\Collection;
use App\Repository\ExtendedEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Component\HttpFoundation\Response;

class CollectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Collection::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Название'),
        ];
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param Collection                           $entityInstance
     *
     * @return void
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $entityInstance->setOwner($this->getUser());

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Doctrine\DBAL\Exception
     */
    public function detail(AdminContext $context): KeyValueStore|Response
    {
        /** @var Collection $collection */
        $collection = $context->getEntity();

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($collection->getOwner()?->getId() === $user->getId()) {
            throw new Exception('Просмотр этой коллекции не доступен этому пользователю');
        }

        parent::detail($context);
    }

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param Collection                           $entityInstance
     *
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($entityInstance->getOwner()?->getId() !== $user->getId()) {
            throw new Exception('Удаление этой коллекции не доступно этому пользователю');
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($entityInstance->getOwner()?->getId() !== $user->getId()) {
            throw new Exception('Обновление этой коллекции не доступно этому пользователю');
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();

        $services[EntityRepository::class] = ExtendedEntityRepository::class;

        return $services;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        /** @var ExtendedEntityRepository $entityRepository */
        $entityRepository = $this->container->get(EntityRepository::class);

        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $entityRepository->createQueryBuilderWithWhere(
            $searchDto,
            $entityDto,
            $fields,
            $filters,
            ['entity.owner = ' . $user->getId()]
        );
    }
}
