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
    // Codi que controla el registre dels meus nous usuaris
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, Security $security): Response
    {
        // Creo una nova entitat on ficare les coses de l'usuari
        $user = new User();
        // Genero el meu formulari basant-me en els camps que vaig demanar per el "RegistrationFormType"
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // Contemplo que ha passat per sota via el botó d'enviament...
        if ($form->isSubmitted() && $form->isValid()) {
            // Hashejo la password secreta per seguretat i l'amago codificada perquè si hi ha un atac no ho puguin llegir!
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData() // Agafo la contrasenya oculta
                )
            );

            // Afegeixo aquest compte de l'usuari a la meva base de dades
            $entityManager->persist($user);
            $entityManager->flush();

            // Envío el Flash verd
            $this->addFlash('success', 'Registre completat amb èxit! Has iniciat sessió automàticament.');

            // L'usuari acaba de fer registre total... Així per simplitzar el procés el lligo com que ja ha entrat d'un sol cop. Directe per jugar.
            return $security->login($user, \App\Security\LoginFormAuthenticator::class, 'main') ?: $this->redirectToRoute('app_home');
        }

        // Mostro en general tota la base en format Twig
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
