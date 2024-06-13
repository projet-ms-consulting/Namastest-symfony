<?php

namespace App\Entity;

use App\Repository\TemplateTestRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TemplateTestRepository::class)
 */
class TemplateTest
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"template_test","templates_api"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     * * @Assert\Length(min=1, max=128,
     *     minMessage="Le nom de votre template de test doit comporter 1 caractère au minimum",
     *     maxMessage="Le nom de votre template de test  doit comporter 128 caractères au maximum")
     * @Groups({"template_test","templates_api"})
     */
    private $nom;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Assert\Length(min=1, max=65535,
     *     minMessage="La description de votre template de test doit comporter 1 caractère au minimum",
     *     maxMessage="La description de votre template de test  doit comporter 65535 caractères au maximum")
     * @Groups({"template_test","templates_api"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\Type("DateTime")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="integer")
     */
    private $version;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="templates")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"template_test","templates_api"})
     */
    private $projet;

    /**
     * @ORM\OneToMany(targetEntity=Test::class, mappedBy="template")
     */
    private $tests;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    /**
     * @ORM\OneToMany(targetEntity=TemplateCatalogueRelation::class, mappedBy="template", orphanRemoval=true, cascade={"persist"})
     * @OrderBy({"ordre" = "DESC"})
     */
    private $templateCatalogueRelation;

    /**
     * @ORM\OneToMany(targetEntity=Commentaire::class, mappedBy="templateTest", orphanRemoval=true)
     */
    private $commentaires;


    public function __construct()
    {
        $this->templateCatalogueRelation = new ArrayCollection();
        $this->tests = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
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

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getVersion(): ?int
    {
        return $this->version;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

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
            $test->setTemplate($this);
        }

        return $this;
    }

    public function removeTest(Test $test): self
    {
        if ($this->tests->removeElement($test)) {
            // set the owning side to null (unless already changed)
            if ($test->getTemplate() === $this) {
                $test->setTemplate(null);
            }
        }

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

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
            $templateCatalogueRelation->setTemplate($this);
        }

        return $this;
    }

    public function removeTemplateCatalogueRelation(TemplateCatalogueRelation $templateCatalogueRelation): self
    {
        if ($this->templateCatalogueRelation->removeElement($templateCatalogueRelation)) {
            // set the owning side to null (unless already changed)
            if ($templateCatalogueRelation->getTemplate() === $this) {
                $templateCatalogueRelation->setTemplate(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Commentaire>
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaire $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setTemplateTest($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaire $commentaire): self
    {
        if ($this->commentaires->removeElement($commentaire)) {
            // set the owning side to null (unless already changed)
            if ($commentaire->getTemplateTest() === $this) {
                $commentaire->setTemplateTest(null);
            }
        }

        return $this;
    }

    public function __toString(){

        return $this->getNom();
    }
}
