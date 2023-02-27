<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PulabiletowMaRodzajebiletow
 *
 * @ORM\Table(name="pulabiletow_ma_rodzajebiletow", indexes={@ORM\Index(name="fk_PulaBiletow_ma_RodzajeBiletow_PuleBiletow1_idx", columns={"PuleBiletow_id"}), @ORM\Index(name="fk_PulaBiletow_ma_RodzajeBiletow_RodzajeBiletow1_idx", columns={"RodzajeBiletow_id"})})
 * @ORM\Entity
 */
class PulabiletowMaRodzajebiletow
{
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
    public function getCena(): string
    {
        return $this->cena;
    }

    /**
     * @param string $cena
     */
    public function setCena(string $cena): void
    {
        $this->cena = $cena;
    }

    /**
     * @return Pulebiletow
     */
    public function getPulebiletow(): Pulebiletow
    {
        return $this->pulebiletow;
    }

    /**
     * @param Pulebiletow $pulebiletow
     */
    public function setPulebiletow(Pulebiletow $pulebiletow): void
    {
        $this->pulebiletow = $pulebiletow;
    }

    /**
     * @return Rodzajebiletow
     */
    public function getRodzajebiletow(): Rodzajebiletow
    {
        return $this->rodzajebiletow;
    }

    /**
     * @param Rodzajebiletow $rodzajebiletow
     */
    public function setRodzajebiletow(Rodzajebiletow $rodzajebiletow): void
    {
        $this->rodzajebiletow = $rodzajebiletow;
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
     * @ORM\Column(name="cena", type="decimal", precision=5, scale=2, nullable=false)
     */
    private $cena;

    /**
     * @var Pulebiletow
     *
     * @ORM\ManyToOne(targetEntity="Pulebiletow", inversedBy="pulaMaRodzajeBiletow")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="PuleBiletow_id", referencedColumnName="id")
     * })
     */
    private $pulebiletow;

    /**
     * @var Rodzajebiletow
     *
     * @ORM\ManyToOne(targetEntity="Rodzajebiletow")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="RodzajeBiletow_id", referencedColumnName="id")
     * })
     */
    private $rodzajebiletow;


}
