<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use App\Enum\WebhookEventAction;
use App\Message\WebhookEvent;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class PostCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Post')
            ->setEntityLabelInPlural('Posts')
            ->setSearchFields(['title', 'url', 'domain', 'author.email', 'author.discordUsername'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('title');
        yield ChoiceField::new('type');
        yield UrlField::new('url');
        yield TextareaField::new('description')->hideOnIndex();
        yield BooleanField::new('domainIsPersonal');
        yield TextField::new('originalAuthorName')->hideOnIndex();
        yield TextField::new('domain')->hideOnForm();
        yield AssociationField::new('author');
        yield DateTimeField::new('createdAt')->hideOnForm();
        yield IntegerField::new('points', 'Votes')
            ->formatValue(function ($value, $entity) {
                return $value;
            })
            ->hideOnForm();

        yield AssociationField::new('comments')
            ->setFormTypeOption('by_reference', false)
            ->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewOnSite = Action::new('viewOnSite', 'View on Site', 'fa fa-eye')
            ->linkToUrl(function (Post $post) {
                return $this->generateUrl('app_post_show', ['id' => $post->getId()]);
            })
            ->setCssClass('btn btn-primary');

        $triggerPostPublishEvent = Action::new('triggerPostPublishEvent', 'Trigger Post Publish', 'fa fa-bolt')
            ->linkToCrudAction('triggerPostPublishEvent')
        ;

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $triggerPostPublishEvent)
            ->add(Crud::PAGE_DETAIL, $triggerPostPublishEvent)
            ->add(Crud::PAGE_DETAIL, $viewOnSite)
            ->add(Crud::PAGE_EDIT, $viewOnSite);
    }
    public function triggerPostPublishEvent(AdminUrlGenerator $adminUrlGenerator): Response
    {
        /** @var Post $post */
        $post = $this->getContext()->getEntity()->getInstance();

        $this->messageBus->dispatch(new WebhookEvent(
            WebhookEventAction::POST_PUBLISHED,
            [
                'postId' => $post->getId(),
                'author' => $post->getAuthor()->getId(),
                'author_discord_id' => $post->getAuthor()->getDiscordId(),
            ]
        ));

        $this->addFlash('success', 'Post Publish Triggered');
    }
}
