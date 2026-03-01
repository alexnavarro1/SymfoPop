<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    // Construeixo l'aspecte base per el camp del registre del meu nou compte
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Aquest és el camp obligatori per a obtenir exclusivament el meu corrent nom real
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => ['class' => 'form-control'],
                'constraints' => [ // Afegeixo el límit assert necessari directament si està en blamc.
                    new NotBlank([
                        'message' => 'Si us plau, introdueix el teu nom',
                    ]),
                ],
            ])
            // Al posar el meu propi email haurà d'assegurar ser d'un format correu normal i el mapejo
            ->add('email', EmailType::class, [
                'label' => 'Correu electrònic',
                'attr' => ['class' => 'form-control'],
            ])
            // Ho demano en un petit box per obligar a l'usuari a marcar els termes. Com no vull a BDD dic mapejat Fals
            ->add('agreeTerms', CheckboxType::class, [
                'label' => "Accepto les condicions d'ús",
                'mapped' => false,
                'attr' => ['class' => 'form-check-input'],
                'constraints' => [ // Verifico que està activat manualment des del formulari per seguir
                    new IsTrue([
                        'message' => 'Has d\'acceptar els termes i condicions.',
                    ]),
                ],
            ])
            // Així és com deixo llegir la comanda oculta codificada amagant sense donar res al meu producte directament  (Mappeig False i en form "Text")
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Contrasenya',
                'mapped' => false, // Aquí és la base on em salto a l'ordinador sense enviar l'objecte fins ser guardjada!
                'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                'constraints' => [ // Asserts aplicats bàsics (Miníma llargada segons documentació d'exercici Symfony)!
                    new NotBlank([
                        'message' => 'Si us plau, introdueix una contrasenya',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'La contrasenya ha de tenir com a mínim {{ limit }} caràcters',
                        'max' => 4096, // Posi-ho màxim a symfony per defecte de capacitat normal de Hasheig.
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
