<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    // Defineixo la ruta pel formulari de login
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si ja he iniciat sessió, em redirigeixo al llistat de productes perquè no té sentit tornar-me a loguejar
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Obtinc l'error d'inici de sessió (si n'hi ha algun)
        $error = $authenticationUtils->getLastAuthenticationError();
        // Recupero l'últim correu electrònic que he introduït per no haver de tornar-lo a escriure
        $lastUsername = $authenticationUtils->getLastUsername();

        // Renoritzo la vista amb les dades
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // Ruta per tancar la sessió
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony s'encarrega d'interceptar aquesta ruta a través del firewall configurat, així que no faig res aquí
        throw new \LogicException('Mètode interceptat pel logout key del firewall.');
    }
}
