<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileEditType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile', name: 'app_profile_')]
final class ProfileController extends AbstractController
{
    #[Route('/', name: 'index')]
    #[Route('/{id:user}', name: 'user')]
    public function index(
        ?User $user = null,
    ): Response
    {
        if (is_null($user) ) {
            $user = $this->getUser();
        }
        
        if (is_null($user)) {
            return $this->redirectToRoute('app_login');
        }
        
        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/edit', name: 'edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response
    {
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
        
        return $this->render('profile/edit.html.twig', [
            'form' => $form,
        ]);
    }
}
