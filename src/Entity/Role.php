<?php

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoleRepository::class)
 */
class Role
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $libelle;

    /**
     * @ORM\ManyToMany(targetEntity=Droit::class, inversedBy="roles")
     */
    private $droits;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="roles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    public function __construct()
    {
        $this->droits = new ArrayCollection();
    }

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

    /**
     * @return Collection|Droit[]
     */
    public function getDroits(): Collection
    {
        return $this->droits;
    }

    public function addDroit(Droit $droit): self
    {
        if (!$this->droits->contains($droit)) {
            $this->droits[] = $droit;
            $droit->addRole($this);
        }

        return $this;
    }

    public function removeDroit(Droit $droit): self
    {
        if ($this->droits->removeElement($droit)) {
            $droit->removeRole($this);
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

    public function __toString(): string
    {
        return $this->libelle;
    }
}
