<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Pulebiletow
 *
 * @ORM\Table(name="pulebiletow", uniqueConstraints={@ORM\UniqueConstraint(name="idPuleBiletow_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="NazwaPuli_UNIQUE", columns={"nazwa"})})
 * @ORM\Entity(repositoryClass="App\Repository\PuleRepository")
 * @UniqueEntity(
 *     fields={"nazwa"},
 *     errorPath="nazwa",
 *     message="Podana wartość już istnieje."
 * )
 */
class Pulebiletow
{
    public function __construct()
    {
        $this->pulaMaRodzajeBiletow = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getNazwa(): ?string
    {
        return $this->nazwa;
    }

    /**
     * @param string $nazwa
     */
    public function setNazwa(string $nazwa): void
    {
        $this->nazwa = $nazwa;
    }

    /**
     * @return bool|null
     */
    public function getUsunieto(): ?bool
    {
        return $this->usunieto;
    }

    /**
     * @param bool|null $usunieto
     */
    public function setUsunieto(?bool $usunieto): void
    {
        $this->usunieto = $usunieto;
    }
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nazwa", type="string", length=45, nullable=false)
     * @Assert\Regex(
     *     pattern="/^[\p{L}\d\s\-]+$/u",
     *     message="Nazwa powinna się składać tylko z liter, spacji, myślników i cyfr."
     * )
     * @Assert\Length(
     *     max = 45,
     *     min = 3,
     *     maxMessage = "Nazwa może zawierać maksymalnie 45 znaków.",
     *     minMessage = "Nazwa musi zawierać minimum 3 znaki."
     * )
     */
    private $nazwa;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="usunieto", type="boolean", nullable=true)
     */
    private $usunieto;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PulabiletowMaRodzajebiletow", mappedBy="pulebiletow")
     */
    private $pulaMaRodzajeBiletow;

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPulaMaRodzajeBiletow(): \Doctrine\Common\Collections\Collection
    {
        return $this->pulaMaRodzajeBiletow;
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Seanse", mappedBy="pulebiletow")
     */
    private $seanse;

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSeanse(): \Doctrine\Common\Collections\Collection
    {
        return $this->seanse;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $seanse
     */
    public function setSeanse(\Doctrine\Common\Collections\Collection $seanse): void
    {
        $this->seanse = $seanse;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $pulaMaRodzajeBiletow
     */
    public function setPulaMaRodzajeBiletow(\Doctrine\Common\Collections\ArrayCollection $pulaMaRodzajeBiletow): void
    {
        $this->pulaMaRodzajeBiletow = $pulaMaRodzajeBiletow;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->nazwa;
    }
}
