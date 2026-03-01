<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    /**
     * Mostra i tramita el procés de registre d'un nou compte d'usuari
     * Accessible únicament per a convidats que desitgin donar-se d'alta com a membres
     */
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = new User();
        // Genero el meu formulari de registre acoblat al voltant dels requeriments indicats
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Si han superat els paràmetres com l'acceptació de termes o les validacions de l'Entity
        if ($form->isSubmitted() && $form->isValid()) {
            // Empro el servei de Symfony per emmascarar i protegir irreversiblement la contrasenya recollida de forma plana
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData() // Absorbeixo la contrasenya del camp específic
                )
            );

            // Gravo oficialment a la BDD de Symfopop la configuració d'aquest nou membre
            $entityManager->persist($user);
            $entityManager->flush();

            // Allibero l'alerta verda de reconeixement al bloc superior sobre haver completat l'enllaç
            $this->addFlash('success', 'Registre completat amb èxit! Has iniciat sessió automàticament.');

            // Compleixo l'objectiu estricte introduint-lo connectat directament a la sessió actual mitjançant login nativitzat
            return $security->login($user, \App\Security\LoginFormAuthenticator::class, 'main') ?: $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
