<?php

namespace App\Controller\Admin;

use App\Entity\Collection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
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
}
