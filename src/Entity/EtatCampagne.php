<?php

namespace App\Entity;

use App\Repository\EtatCampagneRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EtatCampagneRepository::class)
 */
class EtatCampagne
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $libelle;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="etatsCampagne", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEnPrep;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isEnCours;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isCloturee;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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

    public function getIsEnPrep(): ?bool
    {
        return $this->isEnPrep;
    }

    public function setIsEnPrep(bool $isEnPrep): self
    {
        $this->isEnPrep = $isEnPrep;

        return $this;
    }

    public function getIsEnCours(): ?bool
    {
        return $this->isEnCours;
    }

    public function setIsEnCours(bool $isEnCours): self
    {
        $this->isEnCours = $isEnCours;

        return $this;
    }

    public function getIsCloturee(): ?bool
    {
        return $this->isCloturee;
    }

    public function setIsCloturee(bool $isCloturee): self
    {
        $this->isCloturee = $isCloturee;

        return $this;
    }
}
