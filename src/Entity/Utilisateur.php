<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UtilisateurRepository::class)
 * @UniqueEntity(fields={"email"}, message="Un compte existe déjà avec cette adresse email")
 */
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotNull(message="Votre nom ne peut pas être vide")
     * @Assert\Length(min=2, max=64,
     *     minMessage="Votre nom doit comporter 2 charactères au minimum",
     *     maxMessage="Votre nom doit comporter 64 charactères au maximum")
     * @ORM\Column(type="string", length=64)
     */
    private $nom;

    /**
     * @Assert\NotNull(message="Votre prénom ne peut pas être vide")
     * @Assert\Length(min=2, max=64,
     *     minMessage="Votre prénom doit comporter 2 charactères au minimum",
     *     maxMessage="Votre nom doit comporter 64 charactères au maximum")
     * @ORM\Column(type="string", length=64)
     */
    private $prenom;

    /**
     * @Assert\NotNull(message="L'email ne peut pas être vide")
     * @Assert\Email(message="Vous devez entrer un email valide")
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateInscription;

    /**
     * @ORM\OneToMany(targetEntity=Support::class, mappedBy="utilisateur")
     */
    private $supports;

    /**
     * @ORM\OneToMany(targetEntity=Projet::class, mappedBy="auteur")
     */
    private $projets;

    /**
     * @ORM\OneToMany(targetEntity=Invitation::class, mappedBy="auteur")
     */
    private $invitations;

    /**
     * @ORM\OneToMany(targetEntity=ParticipantProjet::class, mappedBy="utilisateur")
     */
    private $participantProjets;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAdministrateur;

    /**
     * @ORM\OneToMany(targetEntity=SupportReponse::class, mappedBy="expediteur", cascade={"persist", "remove"})
     */
    private $supportReponse;

    /**
     * @ORM\OneToMany(targetEntity=QuestionGeneraleReponse::class, mappedBy="expediteur", cascade={"persist", "remove"})
     */
    private $questionGeneraleReponse;

    /**
     * @ORM\OneToMany(targetEntity=Commentaire::class, mappedBy="auteur", cascade={"persist", "remove"})
     */
    private $commentaires;


    public function __construct()
    {
        if(empty($this->dateInscription)) $this->dateInscription = new \DateTime();
        if(empty($this->isAdministrateur)) $this->isAdministrateur = false;

        $this->supports = new ArrayCollection();
        $this->projets = new ArrayCollection();
        $this->invitations = new ArrayCollection();
        $this->participantProjets = new ArrayCollection();
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

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->dateInscription;
    }

    public function setDateInscription(\DateTimeInterface $dateInscription): self
    {
        $this->dateInscription = $dateInscription;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        if($this->isAdministrateur) $roles[] = 'ROLE_ADMIN';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Support>
     */
    public function getSupports(): Collection
    {
        return $this->supports;
    }

    public function addSupport(Support $support): self
    {
        if (!$this->supports->contains($support)) {
            $this->supports[] = $support;
            $support->setUtilisateur($this);
        }

        return $this;
    }

    public function removeSupport(Support $support): self
    {
        if ($this->supports->removeElement($support)) {
            // set the owning side to null (unless already changed)
            if ($support->getUtilisateur() === $this) {
                $support->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Projet>
     */
    public function getProjets(): Collection
    {
        return $this->projets;
    }

    public function addProjet(Projet $projet): self
    {
        if (!$this->projets->contains($projet)) {
            $this->projets[] = $projet;
            $projet->setAuteur($this);
        }

        return $this;
    }

    public function removeProjet(Projet $projet): self
    {
        if ($this->projets->removeElement($projet)) {
            // set the owning side to null (unless already changed)
            if ($projet->getAuteur() === $this) {
                $projet->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getInvitations(): Collection
    {
        return $this->invitations;
    }

    public function addInvitation(Invitation $invitation): self
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations[] = $invitation;
            $invitation->setAuteur($this);
        }

        return $this;
    }

    public function removeInvitation(Invitation $invitation): self
    {
        if ($this->invitations->removeElement($invitation)) {
            // set the owning side to null (unless already changed)
            if ($invitation->getAuteur() === $this) {
                $invitation->setAuteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ParticipantProjet>
     */
    public function getParticipantProjets(): Collection
    {
        return $this->participantProjets;
    }

    public function addParticipantProjet(ParticipantProjet $participantProjet): self
    {
        if (!$this->participantProjets->contains($participantProjet)) {
            $this->participantProjets[] = $participantProjet;
            $participantProjet->setUtilisateur($this);
        }

        return $this;
    }

    public function removeParticipantProjet(ParticipantProjet $participantProjet): self
    {
        if ($this->participantProjets->removeElement($participantProjet)) {
            // set the owning side to null (unless already changed)
            if ($participantProjet->getUtilisateur() === $this) {
                $participantProjet->setUtilisateur(null);
            }
        }

        return $this;
    }

    public function getIsAdministrateur(): ?bool
    {
        return $this->isAdministrateur;
    }

    public function setIsAdministrateur(bool $isAdministrateur): self
    {
        $this->isAdministrateur = $isAdministrateur;

        return $this;
    }

    public function getSupportReponse(): ?SupportReponse
    {
        return $this->supportReponse;
    }

    public function setSupportReponse(SupportReponse $supportReponse): self
    {
        // set the owning side of the relation if necessary
        if ($supportReponse->getExpediteur() !== $this) {
            $supportReponse->setExpediteur($this);
        }

        $this->supportReponse = $supportReponse;

        return $this;
    }

    public function getQuestionGeneraleReponse(): ?QuestionGeneraleReponse
    {
        return $this->questionGeneraleReponse;
    }

    public function setQuestionGeneraleReponse(QuestionGeneraleReponse $questionGeneraleReponse): self
    {
        // set the owning side of the relation if necessary
        if ($questionGeneraleReponse->getExpediteur() !== $this) {
            $questionGeneraleReponse->setExpediteur($this);
        }

        $this->questionGeneraleReponse = $questionGeneraleReponse;

        return $this;
    }

    public function getCommentaire(): ?Commentaire
    {
        return $this->commentaires;
    }

    public function setCommentaire(Commentaire $commentaire): self
    {
        // set the owning side of the relation if necessary
        if ($commentaire->getAuteur() !== $this) {
            $commentaire->setAuteur($this);
        }

        $this->commentaire = $commentaire;

        return $this;
    }

    public function getFullName(): string
    {
        return mb_strtoupper($this->getNom()) . ' ' . mb_convert_case($this->getPrenom(), MB_CASE_TITLE);
    }

    public function __toString(): string
    {
        return $this->getFullName();
    }
}
