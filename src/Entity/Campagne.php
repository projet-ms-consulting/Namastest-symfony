<?php

namespace App\Entity;

use App\Repository\CampagneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
/* use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; */

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/* ----------------------------------------------------------------------------------------------------------------------------------
            UniqueEntity : permet de déclarer l'unicité d'une entité en fonction de l'attribut fields
            voici le lien de la doc au cas ou : https://symfony.com/doc/current/reference/constraints/UniqueEntity.html
            Attention : dans le controller ajouter le projet à la campagne avant de lier l'sinstance au formulaire, par exemple :
                $campagne = new Campagne();
                $campagne->setProjet($projet); <-------- ICI

                $form = $this->createForm(CampagneType::class, $campagne);  <-----  AVANT cette ligne
                $form->handleRequest($request);


            J'ai enlevé le blocage d'un nom de campagne unique car ça pourrait être utile à plusieurs corps de métiers.
            A moins qu'elle doive être unique par rapport à plusieurs champs ?

            Voici la ligne de code :

   -----------------------------------------------------------------------------------------------------------------------------
*/

/**
 *
 * @ORM\Entity(repositoryClass=CampagneRepository::class)
 * @UniqueEntity(fields={"nom", "projet"}, message="Une campagne de ce nom existe déjà", errorPath="nom")
 *
 */
class Campagne
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotNull(message="Le nom de la campagne ne peut pas être vide")
     * @Assert\Length(max=128, min=2,
     *     maxMessage="Le nom de la campagne ne doit pas dépasser 128 caractères",
     *     minMessage="Le nom de la campagne ne peut être plus petit à 128")
     * @ORM\Column(type="string", length=128)
     */
    private $nom;

    /**
     * @Assert\Length(min=2, max=65535,
     *     minMessage="La description de votre campagne doit comporter 10 caractères au minimum",
     *     maxMessage="La description de votre campagne doit comporter 65535 caractères au maximum")
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @Assert\GreaterThanOrEqual(value="now -1day", message="La date de début de la campagne ne peut être inférieure à la date d'aujourd'hui")
     *
     * @ORM\Column(type="datetime", nullable=true) // <----- J'ai mis nullable = true car l'utilisateur pourra entrer une date
     *                                             //        plus tard après la création de la campagne, durant la phase de préparation
     */
    private $dateDebutEstimee;

    /**
     * @Assert\Expression(
     *     "this.getDateFinEstimee() >= this.getDateDebutEstimee() || this.getDateFinEstimee() === null",
     *     message="La date de fin de la campagne ne peut être inférieure à la date de début de la campagne")
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateFinEstimee;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="campagnes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\ManyToOne(targetEntity=EtatCampagne::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $etat;

    /**
     * @ORM\OneToMany(targetEntity=Test::class, mappedBy="campagne", cascade={"remove", "persist"})
     */
    private $tests;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateDebutReelle;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateFinReelle;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $data = [];

    public function __construct()
    {
        $this->tests = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDateDebutEstimee(): ?\DateTimeInterface
    {
        return $this->dateDebutEstimee;
    }

    public function setDateDebutEstimee(\DateTimeInterface $dateDebutEstimee = null): self
    {
        if ($dateDebutEstimee !== null)
        {
            $this->dateDebutEstimee = $dateDebutEstimee;
        }


        return $this;
    }

    public function getDateFinEstimee(): ?\DateTimeInterface
    {
        return $this->dateFinEstimee;
    }

    public function setDateFinEstimee(?\DateTimeInterface $dateFinEstimee = null): self
    {
        if ($dateFinEstimee !== null)
        {
            $this->dateFinEstimee = $dateFinEstimee;
        }
        
        return $this;
    }

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }

    public function getEtat(): ?EtatCampagne
    {
        return $this->etat;
    }

    public function setEtat(?EtatCampagne $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection|Test[]
     */
    public function getTests(): Collection
    {
        return $this->tests;
    }

    public function addTest(Test $test): self
    {
        if (!$this->tests->contains($test)) {
            $this->tests[] = $test;
            $test->setCampagne($this);
        }

        return $this;
    }

    public function removeTest(Test $test): self
    {
        if ($this->tests->removeElement($test)) {
            // set the owning side to null (unless already changed)
            if ($test->getCampagne() === $this) {
                $test->setCampagne(null);
            }
        }

        return $this;
    }

    public function getDateDebutReelle(): ?\DateTimeInterface
    {
        return $this->dateDebutReelle;
    }

    public function setDateDebutReelle(?\DateTimeInterface $dateDebutReelle): self
    {
        $this->dateDebutReelle = $dateDebutReelle;

        return $this;
    }

    public function getDateFinReelle(): ?\DateTimeInterface
    {
        return $this->dateFinReelle;
    }

    public function setDateFinReelle(?\DateTimeInterface $dateFinReelle): self
    {
        $this->dateFinReelle = $dateFinReelle;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function __toString()
    {
        return $this->nom;
    }
}
