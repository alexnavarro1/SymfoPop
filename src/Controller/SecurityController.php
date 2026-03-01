<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * Mostra i gestiona el formulari d'inici de sessió de l'aplicació
     * Accessible per a tothom, però repèl els usuaris que ja estan autenticats
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si ja he iniciat sessió, em redirigeixo al llistat de productes perquè no té sentit tornar-me a loguejar
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // Obtinc l'error d'inici de sessió (si n'hi ha algun llançat pel firewall)
        $error = $authenticationUtils->getLastAuthenticationError();
        // Recupero l'últim correu electrònic introduït per evitar forçar a l'usuari a tornar-lo a escriure
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Tanca la sessió activa de l'usuari actual
     * Interceptat de forma nativa pel mòdul de seguretat de Symfony
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Aquest codi mai no s'executarà ja que el firewall "main" intercepta aquesta ruta directament
        throw new \LogicException('Aquest mètode es troba interceptat per la clau "logout" configurada dins del firewall.');
    }
}
