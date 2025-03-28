<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class CommentCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Comment')
            ->setEntityLabelInPlural('Comments')
            ->setSearchFields(['content', 'author.email', 'author.discordUsername'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextareaField::new('content');
        yield AssociationField::new('author');
        yield AssociationField::new('post');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield BooleanField::new('visible');
        yield TextareaField::new('moderationReason')->hideOnIndex();
        yield DateTimeField::new('moderatedAt')->hideOnForm();
        yield AssociationField::new('moderatedBy')->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        $hideAction = Action::new('hideComment', 'Hide Comment', 'fa fa-eye-slash')
            ->linkToCrudAction('hideComment')
            ->displayIf(static function ($entity) {
                return $entity->isVisible();
            });

        $showAction = Action::new('showComment', 'Show Comment', 'fa fa-eye')
            ->linkToCrudAction('showComment')
            ->displayIf(static function ($entity) {
                return !$entity->isVisible();
            });

        return $actions
            ->add(Crud::PAGE_INDEX, $hideAction)
            ->add(Crud::PAGE_INDEX, $showAction)
            ->add(Crud::PAGE_DETAIL, $hideAction)
            ->add(Crud::PAGE_DETAIL, $showAction);
    }

    public function hideComment(AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $entityManager): Response
    {
        /** @var Comment $comment */
        $comment = $this->getContext()->getEntity()->getInstance();

        $comment->setVisible(false);
        $comment->setModeratedAt(new DateTimeImmutable());
        $comment->setModeratedBy($this->getUser());

        $entityManager->flush();

        $this->addFlash('success', 'Comment has been hidden.');

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function showComment(AdminUrlGenerator $adminUrlGenerator, EntityManagerInterface $entityManager): Response
    {
        /** @var Comment $comment */
        $comment = $this->getContext()->getEntity()->getInstance();

        $comment->setVisible(true);
        $comment->setModeratedAt(new DateTimeImmutable());
        $comment->setModeratedBy($this->getUser());

        $entityManager->flush();

        $this->addFlash('success', 'Comment has been restored.');

        $url = $adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        return $this->redirect($url);
    }
}
