<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * Gestiona exclusivament l'aterratge general de la pàgina
     * Forma de passarel·la directa principal lligada al catàleg index dels productes
     */
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // En lloc de donar una estèril vista principal (home), evito repeticions redigin directament on toca.
        return $this->redirectToRoute('app_product_index');
    }
}
