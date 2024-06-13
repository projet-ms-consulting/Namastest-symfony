<?php

namespace App\Entity;

use App\Repository\EtatTestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EtatTestRepository::class)
 */
class EtatTest
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
     * @ORM\Column(type="boolean")
     */
    private $isATester;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isOK;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isKO;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="etatsTests")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

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

    public function getIsATester(): ?bool
    {
        return $this->isATester;
    }

    public function setIsATester(bool $isATester): self
    {
        $this->isATester = $isATester;

        return $this;
    }

    public function getIsOK(): ?bool
    {
        return $this->isOK;
    }

    public function setIsOK(bool $isOK): self
    {
        $this->isOK = $isOK;

        return $this;
    }

    public function getIsKO(): ?bool
    {
        return $this->isKO;
    }

    public function setIsKO(bool $isKO): self
    {
        $this->isKO = $isKO;

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

    public function __toString()
    {
        return $this->libelle;
    }
}
