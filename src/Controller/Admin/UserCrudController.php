<?php

namespace App\Controller\Admin;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User')
            ->setEntityLabelInPlural('Users')
            ->setSearchFields(['email', 'discordUsername'])
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield EmailField::new('email');

        if (Crud::PAGE_NEW === $pageName) {
            yield TextField::new('password')
                ->setFormType(PasswordType::class)
                ->setRequired(true);
        } elseif (Crud::PAGE_EDIT === $pageName) {
            yield TextField::new('password')
                ->setFormType(PasswordType::class)
                ->setRequired(false)
                ->setHelp('Leave blank to keep the current password');
        }

        yield ArrayField::new('roles');
        yield TextField::new('discordId')->hideOnForm();
        yield TextField::new('discordUsername')->hideOnForm();
        yield TextField::new('avatarUrl')->hideOnForm();
        yield ImageField::new('avatarUrl')
            ->setLabel('Avatar')
            ->onlyOnIndex()
            ->setTemplatePath('admin/field/user_avatar.html.twig');

        yield BooleanField::new('banned');
        yield DateTimeField::new('bannedAt')->hideOnForm();
        yield TextareaField::new('banReason')->hideOnIndex();
        yield AssociationField::new('bannedBy')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        $banUser = Action::new('banUser', 'Ban User', 'fa fa-ban')
            ->linkToCrudAction('banUser')
            ->displayIf(static function ($entity) {
                return !$entity->isBanned();
            });

        $unbanUser = Action::new('unbanUser', 'Unban User', 'fa fa-check')
            ->linkToCrudAction('unbanUser')
            ->displayIf(static function ($entity) {
                return $entity->isBanned();
            });

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Create Admin User');
            });
    }

    public function createEntity(string $entityFqcn)
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        return $user;
    }

    /**
     * @param User $entityInstance
     */
    public function persistEntity($entityFqcn, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::persistEntity($entityFqcn, $entityInstance);
    }

    /**
     * @param User $user
     */
    private function hashPassword(User $user): void
    {
        if ($user->getPassword() === '') {
            return;
        }

        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $user->getPassword()
        );

        $user->setPassword($hashedPassword);
    }

    /**
     * @param User $entityInstance
     */
    public function updateEntity($entityFqcn, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::updateEntity($entityFqcn, $entityInstance);
    }

    public function banUser(AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getContext()->getEntity()->getInstance();

        $user->setBanned(true);
        $user->setBannedAt(new DateTimeImmutable());
        $user->setBannedBy($this->getUser());

        $entityManager->flush();

        $this->addFlash('success', 'User has been banned.');

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function unbanUser(AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getContext()->getEntity()->getInstance();

        $user->setBanned(false);
        $user->setBannedAt(null);
        $user->setBanReason(null);

        $entityManager->flush();

        $this->addFlash('success', 'User has been unbanned.');

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}
