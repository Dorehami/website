<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Form\ProfileEditType;
use App\Service\PlausibleAnalyticsService;
use App\Service\UtilityService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile', name: 'app_profile_')]
final class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    #[Route('/{id:user}', name: 'user', requirements: ['id' => '^(?!edit$).+'])]
    public function index(
        ?User $user = null,
        PlausibleAnalyticsService $plausibleAnalyticsService,
        UtilityService $utilityService
    ): Response {
        if (is_null($user)) {
            $user = $this->getUser();
        }

        if (is_null($user)) {
            return $this->redirectToRoute('app_login');
        }

        $postIds = array_map(fn(Post $p) => $p->getId(), $user->getPosts()->toArray());
        $analytics = $plausibleAnalyticsService->getVisitorsByPostIds($postIds);

        $totalVisits = array_reduce(
            $analytics,
            function (int $carry, array $item) {
                return $carry + $item['visits'];
            },
            0
        );

        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'analytics' => $analytics,
            'total_visits' => $totalVisits,
        ]);
    }

    #[Route('/edit', name: 'edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ProfileEditType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'اطلاعات با موفقیت ثبت شد');

                return $this->redirectToRoute('app_profile_index');
            } else {
                $this->addFlash('error', 'ایرادی در ثبت اطلاعات یافت شد');
            }
        }

        $accountUrl = rtrim($params->get('app.keycloak_base_url'), '/') .
            '/realms/' . $params->get('app.keycloak_realm') . '/account';

        return $this->render('profile/edit.html.twig', [
            'form' => $form,
            'keycloak_account_url' => $accountUrl,
        ]);
    }
}
