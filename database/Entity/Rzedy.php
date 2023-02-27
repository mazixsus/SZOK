<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rzedy
 *
 * @ORM\Table(name="rzedy", uniqueConstraints={@ORM\UniqueConstraint(name="idRzedy_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_Rzedy_Sale1_idx", columns={"Sale_id"}), @ORM\Index(name="fk_Rzedy_TypyRzedow1_idx", columns={"TypyRzedow_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\RzedyRepository")
 */
class Rzedy
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
    public function getNumerrzedu(): int
    {
        return $this->numerrzedu;
    }

    /**
     * @param int $numerrzedu
     */
    public function setNumerrzedu(int $numerrzedu): void
    {
        $this->numerrzedu = $numerrzedu;
    }

    /**
     * @return ?Sale
     */
    public function getSale(): ?Sale
    {
        return $this->sale;
    }

    /**
     * @param Sale $sale
     */
    public function setSale(Sale $sale): void
    {
        $this->sale = $sale;
    }

    /**
     * @return ?Typyrzedow
     */
    public function getTypyrzedow(): ?Typyrzedow
    {
        return $this->typyrzedow;
    }

    /**
     * @param Typyrzedow $typyrzedow
     */
    public function setTypyrzedow(Typyrzedow $typyrzedow): void
    {
        $this->typyrzedow = $typyrzedow;
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
     * @ORM\Column(name="numerRzedu", type="integer", nullable=false)
     */
    private $numerrzedu;

    /**
     * @var \Sale
     *
     * @ORM\ManyToOne(targetEntity="Sale", inversedBy="rzedy")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Sale_id", referencedColumnName="id")
     * })
     */
    private $sale;

    /**
     * @var \Typyrzedow
     *
     * @ORM\ManyToOne(targetEntity="Typyrzedow")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="TypyRzedow_id", referencedColumnName="id")
     * })
     */
    private $typyrzedow;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Miejsca", mappedBy="rzedy")
     */
    private $miejsca;

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMiejsca(): \Doctrine\Common\Collections\Collection
    {
        return $this->miejsca;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $miejsca
     */
    public function setMiejsca(\Doctrine\Common\Collections\Collection $miejsca): void
    {
        $this->miejsca = $miejsca;
    }

}
