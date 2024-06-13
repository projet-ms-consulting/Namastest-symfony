<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $pwdConstraints = [
            new Length([
                'min' => 8,
                'minMessage' => 'Votre mot de passe doit faire au minimum {{ limit }} caractères',
                // max length allowed by Symfony for security reasons
                'max' => 4096,
            ]),
            new Regex([
                'message' => 'Votre mot de passe doit comporter au moins un chiffre, une majuscule, une minuscule mais aucun caractère spécial',
                'pattern' => '#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$#',
                'match' => true
            ])
        ];

        // Si le mot de passe est requis, on ajoute la contrainte NotBlank au champ du mot de passe
        if($options['required_pwd']) {
            array_unshift($pwdConstraints, new NotBlank([
                'message' => 'Veuillez entrer un mot de passe',
            ]));
        }

        $builder
            ->add('nom')
            ->add('prenom', null, [
                'label' => 'Prénom'
            ])
            ->add('email', EmailType::class)
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'invalid_message' => 'Le mot de passe et sa confirmation ne correspondent pas',
                'required' => $options['required_pwd'],
                'mapped' => false,
                'constraints' => $pwdConstraints,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Confirmation du mot de passe'],
                'options' => ['attr' => ['autocomplete' => 'new-password']],
            ])
            ->add('captcha', CaptchaType::class, [
                'label' => 'Veuillez recopier ce code',
                'width' => 200,
                'height' => 60,
                'length' => 6,
                'invalid_message' => 'Le captcha n\'a pas été copié correctement',
                'as_url' => true,
                'reload' => true,
            ])
            /*->add('agreeTerms', CheckboxType::class, [
                'label' => 'Accepter les termes',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les termes.',
                    ]),
                ],
            ])*/ //todo : accepter les termes
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'required_pwd' => true,
        ]);

        $resolver->setAllowedTypes('required_pwd', 'bool');
    }
}
