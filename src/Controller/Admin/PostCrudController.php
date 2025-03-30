<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class PostCrudController extends AbstractCrudController
{
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
        yield UrlField::new('url');
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

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $viewOnSite)
            ->add(Crud::PAGE_EDIT, $viewOnSite);
    }
}
