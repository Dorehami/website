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
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
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
            ->setDefaultSort(['joinedAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        // === Tab: Identity ===
        yield FormField::addTab('👤 Identity & Access');
        yield FormField::addFieldset('Login & Display');
        yield FormField::addColumn(6);
        yield IdField::new('id')->hideOnForm();
        yield EmailField::new('email');
        yield TextField::new('displayName')->setHelp('Name shown in the app');

        if ($pageName === Crud::PAGE_NEW) {
            yield TextField::new('password')
                ->setFormType(PasswordType::class)
                ->setHelp('Required on creation');
        } elseif ($pageName === Crud::PAGE_EDIT) {
            yield TextField::new('password')
                ->setFormType(PasswordType::class)
                ->setRequired(false)
                ->setHelp('Leave blank to keep the current password');
        }

        yield ArrayField::new('roles')->setHelp('e.g. ROLE_USER, ROLE_ADMIN');

        yield FormField::addFieldset('Avatar')->addCssClass('mt-3');
        yield FormField::addColumn(6);
        yield TextField::new('avatarUrl')->hideOnIndex();
        yield ImageField::new('avatarUrl')
            ->onlyOnIndex()
            ->setLabel('Avatar')
            ->setTemplatePath('admin/field/user_avatar.html.twig');

        // === Tab: Moderation ===
        yield FormField::addTab('🛡️ Moderation');
        yield FormField::addFieldset('Ban Settings')->setIcon('ban')->collapsible();

        yield BooleanField::new('banned')->setColumns(4);
        yield DateTimeField::new('bannedAt')->hideOnIndex()->setColumns(4);
        yield AssociationField::new('bannedBy')->hideOnIndex()->setColumns(4);
        yield TextareaField::new('banReason')
            ->hideOnIndex()
            ->setHelp('Visible only if banned');

        // === Optional: Notifications (if editable later)
        yield FormField::addTab('🔔 Notifications');
        yield BooleanField::new('receiveCommentEmailNotification')->hideOnIndex();
        yield BooleanField::new('receiveUpvoteEmailNotification')->hideOnIndex();
        yield BooleanField::new('receiveCommentReplyEmailNotification')->hideOnIndex();
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
        if (empty($user->getPassword())) {
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
