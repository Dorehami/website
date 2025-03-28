<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}
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
    }

    public function configureActions(Actions $actions): Actions
    {
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
     * @param User $entityInstance
     */
    public function updateEntity($entityFqcn, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::updateEntity($entityFqcn, $entityInstance);
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
}