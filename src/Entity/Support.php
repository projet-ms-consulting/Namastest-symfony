<?php

namespace App\Entity;

use App\Repository\SupportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SupportRepository::class)
 */
class Support
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=80)
     * @Assert\NotBlank(message="Le champ nom ne peut pas être vide.")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=80)
     * @Assert\NotBlank(message="Le champ prenom ne peut pas être vide.")
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email(message="Votre email n'est pas valide.")
     * @Assert\NotBlank(message="Le champ email ne peut pas être vide.")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le champ objet ne peut pas être vide.")
     */
    private $objet;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Le champ message ne peut pas être vide.")
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="supports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $utilisateur;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateEnvoi;

    /**
     * @ORM\OneToMany(targetEntity=SupportReponse::class, mappedBy="support", cascade={"remove"})
     */
    private $supportReponses;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isArchived;


    public function __construct()
    {
        if(empty($this->dateEnvoi)) $this->dateEnvoi = new \DateTime();

        $this->supportReponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getObjet(): ?string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): self
    {
        $this->objet = $objet;

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

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

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

    /**
     * @return Collection<int, SupportReponse>
     */
    public function getSupportReponses(): Collection
    {
        return $this->supportReponses;
    }

    public function addSupportReponse(SupportReponse $supportReponse): self
    {
        if (!$this->supportReponses->contains($supportReponse)) {
            $this->supportReponses[] = $supportReponse;
            $supportReponse->setSupport($this);
        }

        return $this;
    }

    public function removeSupportReponse(SupportReponse $supportReponse): self
    {
        if ($this->supportReponses->removeElement($supportReponse)) {
            // set the owning side to null (unless already changed)
            if ($supportReponse->getSupport() === $this) {
                $supportReponse->setSupport(null);
            }
        }

        return $this;
    }

    public function getFullName(): string
    {
        return mb_strtoupper($this->getNom()) . ' ' . mb_convert_case($this->getPrenom(), MB_CASE_TITLE);
    }

    public function getIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(bool $isArchived): self
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function __toString()
    {
        return 'Message #' . (string)$this->getId();
    }

}
