<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Miejsca
 *
 * @ORM\Table(name="miejsca", uniqueConstraints={@ORM\UniqueConstraint(name="idMiejsca_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_Miejsca_Rzedy1_idx", columns={"Rzedy_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\MiejscaRepository")
 */
class Miejsca
{
    /**
     * @return int
     */
    public function getId(): ?int
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
     * @return int
     */
    public function getPozycja(): int
    {
        return $this->pozycja;
    }

    /**
     * @param int $pozycja
     */
    public function setPozycja(int $pozycja): void
    {
        $this->pozycja = $pozycja;
    }

    /**
     * @return int
     */
    public function getNumermiejsca(): int
    {
        return $this->numermiejsca;
    }

    /**
     * @param int $numermiejsca
     */
    public function setNumermiejsca(int $numermiejsca): void
    {
        $this->numermiejsca = $numermiejsca;
    }

    /**
     * @return Rzedy
     */
    public function getRzedy(): Rzedy
    {
        return $this->rzedy;
    }

    /**
     * @param Rzedy $rzedy
     */
    public function setRzedy(Rzedy $rzedy): void
    {
        $this->rzedy = $rzedy;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRezerwacje(): \Doctrine\Common\Collections\Collection
    {
        return $this->rezerwacje;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $rezerwacje
     */
    public function setRezerwacje(\Doctrine\Common\Collections\Collection $rezerwacje): void
    {
        $this->rezerwacje = $rezerwacje;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBilety(): \Doctrine\Common\Collections\Collection
    {
        return $this->bilety;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $bilety
     */
    public function setBilety(\Doctrine\Common\Collections\Collection $bilety): void
    {
        $this->bilety = $bilety;
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
     * @var int
     *
     * @ORM\Column(name="pozycja", type="integer", nullable=false)
     */
    private $pozycja;

    /**
     * @var int
     *
     * @ORM\Column(name="numerMiejsca", type="integer", nullable=false)
     */
    private $numermiejsca;

    /**
     * @var \Rzedy
     *
     * @ORM\ManyToOne(targetEntity="Rzedy", inversedBy="miejsca")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Rzedy_id", referencedColumnName="id")
     * })
     */
    private $rzedy;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Rezerwacje", mappedBy="miejsca")
     */
    private $rezerwacje;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Bilety", mappedBy="miejsca")
     */
    private $bilety;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rezerwacje = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
