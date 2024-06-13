<?php

namespace App\Entity;

use App\Repository\QuestionGeneraleReponseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuestionGeneraleReponseRepository::class)
 */
class QuestionGeneraleReponse
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=QuestionGenerale::class, inversedBy="questionGeneraleReponses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $questionGenerale;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateEnvoi;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="questionGeneraleReponse", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $expediteur;

    /**
     * @ORM\Column(type="text")
     */
    private $message;


    public function __construct()
    {
        if(empty($this->dateEnvoi)) $this->dateEnvoi = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionGenerale(): ?QuestionGenerale
    {
        return $this->questionGenerale;
    }

    public function setQuestionGenerale(?QuestionGenerale $questionGenerale): self
    {
        $this->questionGenerale = $questionGenerale;

        return $this;
    }

    public function getDateEnvoi(): ?\DateTimeInterface
    {
        return $this->dateEnvoi;
    }

    public function setDateEnvoi(\DateTimeInterface $dateEnvoi): self
    {
        $this->dateEnvoi = $dateEnvoi;

        return $this;
    }

    public function getExpediteur(): ?Utilisateur
    {
        return $this->expediteur;
    }

    public function setExpediteur(Utilisateur $expediteur): self
    {
        $this->expediteur = $expediteur;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

}
