<?php

namespace App\Entity;

use App\Repository\TemplateCatalogueRelationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(fields={"template", "catalogue"}, message="Ce template est déjà dans ce catalogue", errorPath="catalogue")
 * @ORM\Entity(repositoryClass=TemplateCatalogueRelationRepository::class)
 */
class TemplateCatalogueRelation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TemplateTest::class, inversedBy="templateCatalogueRelation", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     * 
     */
    private $template;

    /**
     * @ORM\ManyToOne(targetEntity=Catalogue::class, inversedBy="templateCatalogueRelation", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     *
     */
    private $catalogue;

    /**
     *
     * @ORM\Column(type="integer")
     *
     */
    private $ordre;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTemplate(): ?TemplateTest
    {
        return $this->template;
    }

    public function setTemplate(?TemplateTest $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function getCatalogue(): ?Catalogue
    {
        return $this->catalogue;
    }

    public function setCatalogue(?Catalogue $catalogue): self
    {
        $this->catalogue = $catalogue;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }
}
