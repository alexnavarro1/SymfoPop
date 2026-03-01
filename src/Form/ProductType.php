<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    // Aquest és el formulari base pel meu objecte "Product" que construïré visualment després
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Afegeixo el camp del títol en format de text curt
        $builder
            ->add('title', TextType::class, [
                'label' => 'Títol',
                'attr' => ['class' => 'form-control'],
            ])
            // Descripció llarga amb una caixa de text tipus "Textarea" amb 5 línies per defecte
            ->add('description', TextareaType::class, [
                'label' => 'Descripció',
                'attr' => ['class' => 'form-control', 'rows' => 5],
            ])
            // Gestiono el preu de l'anunci. Automàticament limito que em permetin lletres amb HTML5 com a nombre limitat de diner (€)
            ->add('price', MoneyType::class, [
                'label' => 'Preu',
                'currency' => 'EUR',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
            ])
            // És l'URL de la imatge tipus fotogràfica opcionals i en canvi random com he dit a les accions (opcional)
            ->add('image', UrlType::class, [
                'label' => 'URL de la imatge',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'help' => 'Opcional. Si ho deixes en blanc s\'assignarà una imatge aleatòria.',
            ])
        ;
    }

    // Assigno l'entitat associada! Així sabrem que ho envio per persistir i validar sota el mateix nom
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
