<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product')]
class ProductController extends AbstractController
{
    /**
     * Mostra el llistat de tots els productes
     * Accessible per a tots els usuaris (autenticats o no)
     */
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        // Obtenim tots els productes ordenats per data de creació (més recents primer)
        $products = $productRepository->findBy([], ['createdAt' => 'DESC']);

        // Mostrem la vista compartida de l'índex indicant que no es vegin els botons d'acció per defecte
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'title' => 'Catàleg de Productes',
            'show_actions' => false,
            'show_new_button' => false,
            'empty_message' => 'No hi ha productes disponibles.',
        ]);
    }

    /**
     * Mostra el llistat només dels productes publicats per l'usuari actual
     * Reutilitza la vista de l'índex amb paràmetres diferents per habilitar el CRUD visual
     */
    #[Route('/my/products', name: 'app_my_products', methods: ['GET'])]
    #[IsGranted('ROLE_USER')] // Requereix usuari autenticat
    public function myProducts(ProductRepository $productRepository): Response
    {
        // Filtrem per obtenir únicament els productes on el creador som nosaltres
        $products = $productRepository->findBy(['owner' => $this->getUser()], ['createdAt' => 'DESC']);

        // Reutilitzem la vista index.html.twig passant paràmetres com "show_actions" a cert
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'title' => 'Els meus productes',
            'show_actions' => true,
            'show_new_button' => true,
            'empty_message' => 'Encara no has publicat cap producte.',
        ]);
    }

    /**
     * Crea un nou producte associat automàticament a l'usuari sessió actual
     * Protegit amb assegurances de validació al formulari
     */
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')] // Requereix usuari autenticat
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        // Si enviem el formulari i ha validat perfectament contra la nostra entitat Product
        if ($form->isSubmitted() && $form->isValid()) {
            // Assignem l'usuari que ha sol·licitat aquesta creació com el propietari de ple dret
            $product->setOwner($this->getUser());
            
            // Si l'usuari ha deixat la imatge buida, li assignem automàticament una aleatòria de Picsum
            if (!$product->getImage()) {
                $product->setImage('https://picsum.photos/seed/' . uniqid() . '/600/400');
            }

            // Persistim el nou producte i l'enviem a la base de dades
            $entityManager->persist($product);
            $entityManager->flush();

            // Despleguem missatge d'èxit de Flash Bag verd segons bootstrap categories
            $this->addFlash('success', 'Producte creat correctament.');

            // Ens movem cap a la vista "Veure detall" per contemplar fidedignament aquest producte
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        // Generem formulari pel render de Twig de "Nou Producte"
        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Mostra la informació detallada i completa d'un producte aïllat
     * Accessible per a tots els usuaris gràcies al ParamConverter
     */
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(?Product $product): Response
    {
        // Gestionem l'error 404 si el producte no existeix directament
        if (!$product) {
            throw $this->createNotFoundException('El producte que intentes visualitzar no existeix al catàleg.');
        }

        // Engeguem la visualització passsant el producte trobat per "Entity Param Converter" al twig
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * Edita un producte existent
     * Només el propietari pot editar el seu producte
     */
    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')] // Requereix usuari autenticat
    public function edit(Request $request, ?Product $product, EntityManagerInterface $entityManager): Response
    {
        // Gestionem de retruc l'error 404
        if (!$product) {
            throw $this->createNotFoundException('El producte que intentes editar ja no existeix.');
        }

        // Validem que l'usuari actual sigui el propietari del producte
        if ($product->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No pots editar aquest producte perquè no ets el seu propietari.');
        }

        // Construim la pre-càrrega del formulari amb dades ja definides anteriorment
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Un sol flush ja registra l'actualització perquè Entity ja coneix que existeix abans d'editat
            $entityManager->flush();

            $this->addFlash('success', 'Producte actualitzat correctament.');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Esborra un producte del sistema via CSRF
     * Validem la persistència com a propietaris evitant atacs aliens
     */
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')] // Requereix usuari autenticat
    public function delete(Request $request, ?Product $product, EntityManagerInterface $entityManager): Response
    {
        // Protegim primer amb 404
        if (!$product) {
            throw $this->createNotFoundException('No s\'ha pogut trobar el producte per esborrar.');
        }

        // Validem que l'usuari actual sigui el propietari del producte a esborrar
        if ($product->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException('No tens permís per esborrar explícitament aquest producte.');
        }

        // Verifiquem si el botó amagat que inclou un token de CSRF per eliminar a Twig concorda aquí
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            // Executem esborrat des del Doctrine cap a les taules
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Producte esborrat correctament.');
        }

        // Retornem globalment al index final d'aquí per confirmar neteja
        return $this->redirectToRoute('app_product_index');
    }
}
