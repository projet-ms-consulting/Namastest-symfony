<?php

namespace App\Entity;

use App\Repository\ProjetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(fields={"auteur", "nom"}, message="Un projet de ce nom existe déjà", errorPath="nom")
 * @ORM\Entity(repositoryClass=ProjetRepository::class)
 */
class Projet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("projet_api")
     */
    private $id;

    /**
     * @Assert\NotNull(message="Le nom du projet ne peut pas être vide")
     * @Assert\Length(min=2, max=128,
     *     minMessage="Le nom du projet doit faire au moins 2 caractères",
     *     maxMessage="Le nom du projet doit faire moins de 128 caractères")
     * @ORM\Column(type="string", length=128)
     * @Groups("projet_api")
     */
    private $nom;


    /**
     * @ORM\Column(type="datetime")
     * @Groups("projet_api")
     */
    private $dateCreation;

    /**
     * @ORM\ManyToOne(targetEntity=Utilisateur::class, inversedBy="projets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $auteur;

    /**
     * @ORM\OneToMany(targetEntity=ParticipantProjet::class, mappedBy="projet", cascade={"remove"})
     */
    private $participantsProjet;

    /**
     * @ORM\OneToMany(targetEntity=TemplateTest::class, mappedBy="projet", cascade={"remove"})
     */
    private $templates;

    /**
     * @ORM\OneToMany(targetEntity=Catalogue::class, mappedBy="projet", cascade={"remove"})
     */
    private $catalogues;

    /**
     * @ORM\OneToMany(targetEntity=Campagne::class, mappedBy="projet", cascade={"remove"})
     */
    private $campagnes;

    /**
     * @ORM\OneToMany(targetEntity=Role::class, mappedBy="projet", orphanRemoval=true)
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="projet", orphanRemoval=true)
     */
    private $invitations;

    /**
     * @ORM\OneToMany(targetEntity=EtatCampagne::class, mappedBy="projet", orphanRemoval=true, cascade={"remove"})
     */
    private $etatsCampagne;

    /**
     * @ORM\OneToMany(targetEntity=EtatTest::class, mappedBy="projet", orphanRemoval=true)
     */
    private $etatsTests;

    public function __construct()
    {
        $this->participantsProjet = new ArrayCollection();
        $this->templates = new ArrayCollection();
        $this->catalogues = new ArrayCollection();
        $this->campagnes = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->etatsCampagne = new ArrayCollection();
        $this->etatsTests = new ArrayCollection();
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


    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getAuteur(): ?Utilisateur
    {
        return $this->auteur;
    }

    public function setAuteur(?Utilisateur $auteur): self
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * @return Collection|ParticipantProjet[]
     */
    public function getParticipantsProjet(): Collection
    {
        return $this->participantsProjet;
    }

    public function addParticipantsProjet(ParticipantProjet $participantsProjet): self
    {
        if (!$this->participantsProjet->contains($participantsProjet)) {
            $this->participantsProjet[] = $participantsProjet;
            $participantsProjet->setProjet($this);
        }

        return $this;
    }

    public function removeParticipantsProjet(ParticipantProjet $participantsProjet): self
    {
        if ($this->participantsProjet->removeElement($participantsProjet)) {
            // set the owning side to null (unless already changed)
            if ($participantsProjet->getProjet() === $this) {
                $participantsProjet->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TemplateTest[]
     */
    public function getTemplates(): Collection
    {
        return $this->templates;
    }

    public function addTemplate(TemplateTest $template): self
    {
        if (!$this->templates->contains($template)) {
            $this->templates[] = $template;
            $template->setProjet($this);
        }

        return $this;
    }

    public function removeTemplate(TemplateTest $template): self
    {
        if ($this->templates->removeElement($template)) {
            // set the owning side to null (unless already changed)
            if ($template->getProjet() === $this) {
                $template->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Catalogue[]
     */
    public function getCatalogues(): Collection
    {
        return $this->catalogues;
    }

    public function addCatalogue(Catalogue $catalogue): self
    {
        if (!$this->catalogues->contains($catalogue)) {
            $this->catalogues[] = $catalogue;
            $catalogue->setProjet($this);
        }

        return $this;
    }

    public function removeCatalogue(Catalogue $catalogue): self
    {
        if ($this->catalogues->removeElement($catalogue)) {
            // set the owning side to null (unless already changed)
            if ($catalogue->getProjet() === $this) {
                $catalogue->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Campagne[]
     */
    public function getCampagnes(): Collection
    {
        return $this->campagnes;
    }

    public function addCampagne(Campagne $campagne): self
    {
        if (!$this->campagnes->contains($campagne)) {
            $this->campagnes[] = $campagne;
            $campagne->setProjet($this);
        }

        return $this;
    }

    public function removeCampagne(Campagne $campagne): self
    {
        if ($this->campagnes->removeElement($campagne)) {
            // set the owning side to null (unless already changed)
            if ($campagne->getProjet() === $this) {
                $campagne->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Role[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setProjet($this);
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        if ($this->roles->removeElement($role)) {
            // set the owning side to null (unless already changed)
            if ($role->getProjet() === $this) {
                $role->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Invitation[]
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addInvitation(Invitation $invitation): self
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations[] = $invitation;
            $invitation->setProjet($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): self
    {
        if ($this->invitations->removeElement($invitation)) {
            // set the owning side to null (unless already changed)
            if ($invitation->getProjet() === $this) {
                $invitation->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EtatCampagne[]
     */
    public function getEtatsCampagne(): Collection
    {
        return $this->etatsCampagne;
    }

    public function addEtatsCampagne(EtatCampagne $etatsCampagne): self
    {
        if (!$this->etatsCampagne->contains($etatsCampagne)) {
            $this->etatsCampagne[] = $etatsCampagne;
            $etatsCampagne->setProjet($this);
        }

        return $this;
    }

    public function removeEtatsCampagne(EtatCampagne $etatsCampagne): self
    {
        if ($this->etatsCampagne->removeElement($etatsCampagne)) {
            // set the owning side to null (unless already changed)
            if ($etatsCampagne->getProjet() === $this) {
                $etatsCampagne->setProjet(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EtatTest[]
     */
    public function getEtatsTests(): Collection
    {
        return $this->etatsTests;
    }

    public function addEtatsTest(EtatTest $etatsTest): self
    {
        if (!$this->etatsTests->contains($etatsTest)) {
            $this->etatsTests[] = $etatsTest;
            $etatsTest->setProjet($this);
        }

        return $this;
    }

    public function removeEtatsTest(EtatTest $etatsTest): self
    {
        if ($this->etatsTests->removeElement($etatsTest)) {
            // set the owning side to null (unless already changed)
            if ($etatsTest->getProjet() === $this) {
                $etatsTest->setProjet(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}
