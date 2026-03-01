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
    // Aquesta ruta em mostra tots els productes disponibles al catàleg
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        // Obtinc tots els productes ordenats pel més recent
        $products = $productRepository->findBy([], ['createdAt' => 'DESC']);

        // Mostro la vista index compartida per veure'ls i configurar els botons amagats (només per visualitzar)
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'title' => 'Catàleg de Productes',
            'show_actions' => false,
            'show_new_button' => false,
            'empty_message' => 'No hi ha productes disponibles.',
        ]);
    }

    // Ruta on només jo, com a usuari autenticat, puc entrar per veure els MEUS propis productes
    #[Route('/my/products', name: 'app_my_products', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myProducts(ProductRepository $productRepository): Response
    {
        // Filtro els productes on jo sóc l'owner (propietari)
        $products = $productRepository->findBy(['owner' => $this->getUser()], ['createdAt' => 'DESC']);

        // Reutilitzo la vista index.html.twig, però canvio els títols i permeto accions de modificar i crear
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'title' => 'Els meus productes',
            'show_actions' => true,
            'show_new_button' => true,
            'empty_message' => 'Encara no has publicat cap producte.',
        ]);
    }

    // Ruta on configuro la creació d'un producte nou. També haig d'estar loguejat.
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Instancio un nou producte amb el qual faré el formulari
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        // Si rebo dades i tot és correcte segons les meves limitacions (Asserts)...
        if ($form->isSubmitted() && $form->isValid()) {
            // M'assigno automàticament com el creador d'aquest producte!
            $product->setOwner($this->getUser());
            
            // Em genero una imatge genèrica amb picsum en cas que no en posi una.
            if (!$product->getImage()) {
                $product->setImage('https://picsum.photos/seed/' . uniqid() . '/600/400');
            }

            // Ho deso a la meva base de dades utilitzant el manegador (manager) d'entitats (Entity Manager)
            $entityManager->persist($product);
            $entityManager->flush();

            // Surt un missatge verd dient-me que ho he fet bé (Missatge Flash)
            $this->addFlash('success', 'Producte creat correctament.');

            // Em redirigeixo a veure el que acabo de crear!
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        // Renoritzo la pàgina amb el formulari a través del twig
        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    // Aquesta ruta me'n permet veure exclusivament un de sol fent servir l'identificador
    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        // Prenc l'identificador màgic per ParamConverter que buscarà un producte sol a base de dades i el mostro
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    // Aquí puc modificar un producte que he publicat prèviament. Ho gestiono amb POST/GET
    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        // M'assecuro ràpidament que el producte em pertany abans de tocar-lo (només l'owner ho fa).
        if ($this->getUser() !== $product->getOwner()) {
            throw $this->createAccessDeniedException('No tens permís per editar aquest producte.');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        // Guardo si em premen el botó i les dades són correctes.
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Com ja existia, no cal persistir de nou, n'hi ha prou amb flush.

            $this->addFlash('success', 'Producte actualitzat correctament.');
            return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
        }

        // I la template equivalent...
        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    // Aquesta ruta només funciona per peticions d'avís (POST/DELETE) ocultes des del formulari per esborrar un producte.
    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        // Primer repasso si la foto i el contingut és meu com en Edit.
        if ($this->getUser() !== $product->getOwner()) {
            throw $this->createAccessDeniedException('No tens permís per esborrar aquest producte.');
        }

        // Valido el token CSRF per seguretat (evito operacions de forces malignes d'altres webs).
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            // Em carrego l'entitat a la llista d'esborrats del Manager d'entitats de Doctrine i sincronitzo base de dades
            $entityManager->remove($product);
            $entityManager->flush();
            $this->addFlash('success', 'Producte esborrat correctament.');
        }

        // Sempre acabo de tornada on hi he d'anar (A la general i no la d'usuari directament per fer-ho més simple).
        return $this->redirectToRoute('app_product_index');
    }
}
