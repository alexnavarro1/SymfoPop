<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    // Defineixo la ruta arrel de l'aplicació, el primer lloc on l'usuari aterra
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Per evitar duplicar codi, redirigeixo directament al llistat de productes
        return $this->redirectToRoute('app_product_index');
    }
}
