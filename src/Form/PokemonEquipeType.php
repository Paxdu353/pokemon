<?php

namespace App\Form;

use App\Entity\PokemonEquipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

class PokemonEquipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pokemonId', IntegerType::class, [
                'label' => 'Numéro Pokédex',
                'constraints' => [
                    new NotBlank(message: 'Indiquez le numéro du Pokémon à ajouter.'),
                    new Positive(message: 'Le numéro doit être positif.'),
                ],
            ])
            ->add('surnom', TextType::class, [
                'label' => 'Surnom (optionnel)',
                'required' => false,
                'constraints' => [
                    new Length(max: 100, maxMessage: 'Le surnom ne peut pas dépasser {{ limit }} caractères.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PokemonEquipe::class,
        ]);
    }
}
