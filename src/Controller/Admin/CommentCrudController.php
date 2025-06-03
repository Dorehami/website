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
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class CommentCrudController extends AbstractCrudController
{
    public function __construct()
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
        // === Tab: Content ===
        yield FormField::addTab('💬 Comment');
        yield FormField::addFieldset('Content');
        yield IdField::new('id')->hideOnForm();
        yield TextareaField::new('content')
            ->setHelp('Raw user-submitted comment content.');

        yield BooleanField::new('visible')
            ->setHelp('Toggle visibility (hidden comments are not shown publicly).');

        // === Tab: Relations ===
        yield FormField::addTab('🔗 Relations');
        yield FormField::addFieldset('Linked Entities');

        yield FormField::addColumn(6);
        yield AssociationField::new('author')->setHelp('Comment creator');

        yield FormField::addColumn(6);
        yield AssociationField::new('post')->setHelp('Associated post');

        yield AssociationField::new('parent')
            ->hideOnIndex()
            ->setHelp('If this is a reply, points to the parent comment.');

        yield FormField::addFieldset('Metadata');
        yield FormField::addColumn(6);
        yield DateTimeField::new('createdAt')
            ->hideOnForm()
            ->setHelp('When this comment was originally submitted.');

        // === Tab: Moderation ===
        yield FormField::addTab('🛡 Moderation');
        yield FormField::addFieldset('Moderation Status')->collapsible();

        yield TextareaField::new('moderationReason')
            ->hideOnIndex()
            ->setHelp('Why the comment was moderated (optional).');

        yield DateTimeField::new('moderatedAt')
            ->hideOnForm()
            ->setHelp('Date/time of moderation (read-only)');

        yield AssociationField::new('moderatedBy')
            ->hideOnForm()
            ->setHelp('Admin/moderator who performed the action.');
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
