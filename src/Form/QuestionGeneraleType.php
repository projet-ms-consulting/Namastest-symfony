<?php

namespace App\Form;

use App\Entity\QuestionGenerale;
use Gregwar\CaptchaBundle\Type\CaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionGeneraleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('nom')
            ->add('prenom', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('email', emailType::class)
            ->add('objet', TextType::class)
            ->add('message', TextareaType::class)
            ->add('captcha', CaptchaType::class, [
                'label' => 'Veuillez recopier ce code',
                'width' => 200,
                'height' => 60,
                'length' => 6,
                'invalid_message' => 'Le captcha n\'a pas été copié correctement',
                'as_url' => true,
                'reload' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuestionGenerale::class,
        ]);
    }
}
