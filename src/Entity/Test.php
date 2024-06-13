<?php

namespace App\Entity;

use App\Repository\TestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator as CustomAssert;

/**
 * @CustomAssert\TestConstraint
 * @ORM\Entity(repositoryClass=TestRepository::class)
 */
class Test
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\Length (max=128, min=1,
     *     maxMessage="Le nom ne doit pas dépasser 128 caractères",
     *     minMessage="Le nom ne peut pas être inférieur à 1 caractère")
     * @ORM\Column(type="string", length=128, nullable=true)
     */
    private $nom;

    /**
     * @Assert\Length (max=65535, min=1,
     *     maxMessage="La description ne doit pas dépasser 65535 caractères",
     *     minMessage="La description ne peut pas être inférieure à 1 caractère")
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordre;

    /**
     * @ORM\ManyToOne(targetEntity=Campagne::class, inversedBy="tests", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $campagne;

    /**
     * @ORM\ManyToOne(targetEntity=EtatTest::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity=TemplateTest::class, inversedBy="tests")
     * @ORM\JoinColumn(nullable=true)
     */
    private $template;

    /**
     * @Assert\Length (max=65535, min=1,
     *     maxMessage="Les précisions ne doivent pas dépasser 65535 caractères",
     *     minMessage="Les précisions ne peuvent pas être inférieure à 1 caractère")
     * @ORM\Column(type="text", nullable=true)
     */
    private $precisionsResultat;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
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

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(?int $ordre): self
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getCampagne(): ?Campagne
    {
        return $this->campagne;
    }

    public function setCampagne(?Campagne $campagne): self
    {
        $this->campagne = $campagne;

        return $this;
    }

    public function getEtat(): ?EtatTest
    {
        return $this->etat;
    }

    public function setEtat(?EtatTest $etat): self
    {
        $this->etat = $etat;

        return $this;
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

    public function getPrecisionsResultat(): ?string
    {
        return $this->precisionsResultat;
    }

    public function setPrecisionsResultat(?string $precisionsResultat): self
    {
        $this->precisionsResultat = $precisionsResultat;

        return $this;
    }
}
