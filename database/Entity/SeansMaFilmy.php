<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SeansMaFilmy
 *
 * @ORM\Table(name="seans_ma_filmy", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_Seans_ma_Filmy_Seanse1_idx", columns={"Seanse_id"}), @ORM\Index(name="fk_Seans_ma_Filmy_Filmy1_idx", columns={"Filmy_id"})})
 * @ORM\Entity
 */
class SeansMaFilmy
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
     * @return int
     */
    public function getKolejnosc(): int
    {
        return $this->kolejnosc;
    }

    /**
     * @param int $kolejnosc
     */
    public function setKolejnosc(int $kolejnosc): void
    {
        $this->kolejnosc = $kolejnosc;
    }

    /**
     * @return Filmy
     */
    public function getFilmy(): Filmy
    {
        return $this->filmy;
    }

    /**
     * @param Filmy $filmy
     */
    public function setFilmy(Filmy $filmy): void
    {
        $this->filmy = $filmy;
    }

    /**
     * @return Seanse
     */
    public function getSeanse(): Seanse
    {
        return $this->seanse;
    }

    /**
     * @param Seanse $seanse
     */
    public function setSeanse(Seanse $seanse): void
    {
        $this->seanse = $seanse;
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
     * @ORM\Column(name="kolejnosc", type="integer", nullable=false)
     */
    private $kolejnosc;

    /**
     * @var \Filmy
     *
     * @ORM\ManyToOne(targetEntity="Filmy", inversedBy="seansMaFilmy")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Filmy_id", referencedColumnName="id")
     * })
     */
    private $filmy;

    /**
     * @var \Seanse
     *
     * @ORM\ManyToOne(targetEntity="Seanse", inversedBy="seansMaFilmy")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Seanse_id", referencedColumnName="id")
     * })
     */
    private $seanse;

    public function __toString()
    {
        return (string) $this->filmy;
    }
}
