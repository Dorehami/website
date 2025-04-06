<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\Report;
use App\Entity\ReportableEntity;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;

class ReportCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Report::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Report')
            ->setEntityLabelInPlural('Reports')
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('entityType')
            ->formatValue(function ($value) {
                return match ($value) {
                    Comment::class => 'Comment',
                    Post::class => 'Post',
                    default => $value,
                };
            })
            ->hideOnIndex();
        yield IdField::new('entityId')
            ->hideOnIndex();

        yield TextareaField::new('entity', 'Content')
            ->formatValue(function ($value, $entity) use ($pageName) {
                $reportedEntity = $this->getReportedEntity($entity);
                if ($reportedEntity instanceof ReportableEntity) {
                    return $reportedEntity->getContent();
                }
                return 'Content unavailable';
            })
            ->setVirtual(true)
            ->onlyOnIndex();
        
        yield AssociationField::new('reportedBy');
        yield BooleanField::new('aiFlagged');
        yield BooleanField::new('reportProcessed');
        yield BooleanField::new('humanProcessed');
    }

    private function getReportedEntity(Report $report): ?object
    {
        $entityType = $report->getEntityType();
        $entityId = $report->getEntityId();

        return $this->entityManager->find($entityType, $entityId);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('aiFlagged'))
            ->add(BooleanFilter::new('reportProcessed'))
            ->add(BooleanFilter::new('humanProcessed'));
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewEntity = Action::new('viewEntity', 'View Entity', 'fa fa-eye')
            ->linkToCrudAction('viewEntity');

        $hideContent = Action::new('hideContent', 'Hide Content', 'fa fa-eye-slash')
            ->linkToCrudAction('hideContent')
            ->displayIf(function (Report $report) {
                $entity = $this->getReportedEntity($report);
                if (!$entity instanceof ReportableEntity) {
                    return false;
                }
                try {
                    $reflectionMethod = new ReflectionMethod($entity, 'isVisible');
                    return $entity->isVisible();
                } catch (ReflectionException $e) {
                    // No isVisible method
                    return false;
                }
            });

        $markProcessed = Action::new('markProcessed', 'Mark Processed', 'fa fa-check')
            ->linkToCrudAction('markProcessed')
            ->displayIf(function (Report $report) {
                return !$report->isHumanProcessed();
            });

        return $actions
            ->add(Crud::PAGE_INDEX, $viewEntity)
            ->add(Crud::PAGE_DETAIL, $viewEntity)
            ->add(Crud::PAGE_INDEX, $hideContent)
            ->add(Crud::PAGE_DETAIL, $hideContent)
            ->add(Crud::PAGE_INDEX, $markProcessed)
            ->add(Crud::PAGE_DETAIL, $markProcessed);
    }

    public function viewEntity(AdminUrlGenerator $adminUrlGenerator): Response
    {
        $report = $this->getContext()->getEntity()->getInstance();
        $entity = $this->getReportedEntity($report);

        if (!$entity) {
            $this->addFlash('error', 'Cannot find the content');
            return $this->redirect($this->generateCrudUrl(Action::INDEX));
        }

        // Determine the appropriate controller for the entity type
        $controller = match ($report->getEntityType()) {
            Comment::class => CommentCrudController::class,
            Post::class => PostCrudController::class,
            default => null,
        };

        if ($controller === null) {
            $this->addFlash('error', 'No controllers defined to process this entity');
            return $this->redirect($this->generateCrudUrl(Action::INDEX));
        }

        $url = $adminUrlGenerator
            ->setController($controller)
            ->setAction(Action::DETAIL)
            ->setEntityId($report->getEntityId())
            ->generateUrl();

        return $this->redirect($url);
    }

    private function generateCrudUrl(string $action): string
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $adminUrlGenerator
            ->setController(self::class)
            ->setAction($action)
            ->generateUrl();
    }

    public function hideContent(AdminUrlGenerator $adminUrlGenerator): Response
    {
        $report = $this->getContext()->getEntity()->getInstance();
        $entity = $this->getReportedEntity($report);

        if (!$entity instanceof ReportableEntity) {
            $this->addFlash('error', 'Cannot manage this entity');
            return $this->redirect($this->generateCrudUrl(Action::INDEX));
        }

        $entity->setVisible(false);
        $entity->setModerationReason('Content got hidden by admin');
        $entity->setModeratedAt(new DateTimeImmutable());

        // Mark the report as processed by human
        $report->setHumanProcessed(true);

        $this->entityManager->flush();

        $this->addFlash('success', 'Content hid successfully');

        return $this->redirect($this->generateCrudUrl(Action::INDEX));
    }

    public function markProcessed(AdminUrlGenerator $adminUrlGenerator): Response
    {
        $report = $this->getContext()->getEntity()->getInstance();
        $report->setHumanProcessed(true);

        $this->entityManager->flush();

        $this->addFlash('success', 'Content marked as processed by human');

        return $this->redirect($this->generateCrudUrl(Action::INDEX));
    }
}