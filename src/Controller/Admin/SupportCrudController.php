<?php

namespace App\Controller\Admin;

use App\Entity\Support;
use App\Entity\SupportReponse;
use App\Form\ContactType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class SupportCrudController extends AbstractContactCrudController
{
    public const PAGE_NAME = 'Support technique';

    public static function getEntityFqcn(): string
    {
        return Support::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, self::PAGE_NAME)
            ->setPageTitle(Crud::PAGE_DETAIL, fn (Support $support) => self::PAGE_NAME . ' #' . $support->getId())
            ;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function answer(AdminContext      $context,
                           Request           $request,
                           AdminUrlGenerator $adminUrlGenerator,
                           MailerInterface   $mailer,
                           EntityManagerInterface $entityManager): Response
    {
        $message = $context->getEntity()->getInstance();

        $url = $adminUrlGenerator->unsetAll()
            ->setController(self::class)
            ->setAction(Action::DETAIL)
            ->setEntityId($message->getID());

        $reponse = new SupportReponse();

        $form = $this->createForm(ContactType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = (new TemplatedEmail())
                ->from(new Address('no-reply@namastest.com', 'NamasTest'))
                ->to($message->getEmail())
                ->subject('Re: ' . $message->getObjet())
                ->htmlTemplate('admin/contact/template-mail.html.twig')
                ->context([
                    'message' => $context->getEntity()->getInstance(),
                    'reponse' => $form->getData()
                ]);

            try {
                $mailer->send($email);

                $this->addFlash('success', 'Votre message a bien été envoyé !');

                $reponse->setSupport($message);
                $reponse->setExpediteur($this->getUser());

                $entityManager->persist($reponse);
                $entityManager->flush();

                return $this->redirect($url);
            }
            catch (TransportExceptionInterface $e) {
                $this->addFlash('warning', "Une erreur est survenue lors de l'envoi du mail.");
            }
        }

        return $this->renderForm('admin/contact/repondre-message-form.html.twig', [
            'page' => self::PAGE_NAME,
            'form' => $form,
            'message' => $message,
            'referer' => $url,
        ]);
    }

}
