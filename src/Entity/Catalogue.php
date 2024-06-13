<?php

namespace App\Entity;

use App\Repository\CatalogueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(fields={"libelle", "projet"}, message="Une catalogue de ce libelle existe déjà", errorPath="libelle")
 * @ORM\Entity(repositoryClass=CatalogueRepository::class)
 */
class Catalogue
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotNull(message="Le libelle du catalogue ne peut pas être vide")
     * @Assert\Length(min=2, max=128,
     *     minMessage="Le libelle du catalogue doit faire au moins 2 caractères",
     *     maxMessage="Le libelle du catalogue doit faire moins de 128 caractères")
     * @ORM\Column(type="string", length=128)
     */
    private $libelle;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="catalogues")
     * @ORM\JoinColumn(nullable=false)
     */
    private $projet;

    /**
     * @ORM\OneToMany(targetEntity=TemplateCatalogueRelation::class, mappedBy="catalogue", orphanRemoval=true, cascade={"persist"})
     * @OrderBy({"ordre" = "DESC"})
     */
    private $templateCatalogueRelation;


    public function __construct()
    {
        $this->templateCatalogueRelation = new ArrayCollection();
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

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;

        return $this;
    }

    /**
     * @return Collection|TemplateCatalogueRelation[]
     */
    public function getTemplateCatalogueRelation(): Collection
    {
        return $this->templateCatalogueRelation;
    }

    public function addTemplateCatalogueRelation(TemplateCatalogueRelation $templateCatalogueRelation): self
    {
        if (!$this->templateCatalogueRelation->contains($templateCatalogueRelation)) {
            $this->templateCatalogueRelation[] = $templateCatalogueRelation;
            $templateCatalogueRelation->setCatalogue($this);
        }

        return $this;
    }

    public function removeTemplateCatalogueRelation(TemplateCatalogueRelation $templateCatalogueRelation): self
    {
        if ($this->templateCatalogueRelation->removeElement($templateCatalogueRelation)) {
            // set the owning side to null (unless already changed)
            if ($templateCatalogueRelation->getCatalogue() === $this) {
                $templateCatalogueRelation->setCatalogue(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->libelle;
    }
}
