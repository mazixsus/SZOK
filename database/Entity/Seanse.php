<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Seanse
 *
 * @ORM\Table(name="seanse", uniqueConstraints={@ORM\UniqueConstraint(name="idSeanse_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_Seanse_WydarzeniaSpecjalne1_idx", columns={"WydarzeniaSpecjalne_id"}), @ORM\Index(name="fk_Seanse_TypySeansow1_idx", columns={"TypySeansow_id"}), @ORM\Index(name="fk_Seanse_Sale1_idx", columns={"Sale_id"}), @ORM\Index(name="fk_Seanse_PuleBiletow1_idx", columns={"PuleBiletow_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\SeanseRepository")
 */
class Seanse
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="poczatekSeansu", type="datetime", nullable=false)
     * @Assert\NotNull(message="Początek seansu jest wymagany.")
     * @Assert\GreaterThanOrEqual(
     *     value="tomorrow",
     *     message="Seans nie może rozpoczynać się wcześniej niż jutro."
     *     )
     */
    private $poczatekseansu;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="czyOdwolany", type="boolean", nullable=true)
     */
    private $czyodwolany;

    /**
     * @var \Pulebiletow
     *
     * @ORM\ManyToOne(targetEntity="Pulebiletow")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="PuleBiletow_id", referencedColumnName="id")
     * })
     * @Assert\NotNull(message="Pula biletów jest wymagana.")
     */
    private $pulebiletow;

    /**
     * @var \Sale
     *
     * @ORM\ManyToOne(targetEntity="Sale")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Sale_id", referencedColumnName="id")
     * })
     * @Assert\NotNull(message="Sala jest wymagana.")
     */
    private $sale;

    /**
     * @var \Typyseansow
     *
     * @ORM\ManyToOne(targetEntity="Typyseansow")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="TypySeansow_id", referencedColumnName="id")
     * })
     * @Assert\NotNull(message="Format jest wymagany.")
     */
    private $typyseansow;

    /**
     * @var \Wydarzeniaspecjalne
     *
     * @ORM\ManyToOne(targetEntity="Wydarzeniaspecjalne")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="WydarzeniaSpecjalne_id", referencedColumnName="id")
     * })
     */
    private $wydarzeniaspecjalne;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="SeansMaFilmy", mappedBy="seanse")
     */
    private $seansMaFilmy;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Tranzakcje", mappedBy="seanse")
     */
    private $tranzakcje;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Rezerwacje", mappedBy="seanse")
     */
    private $rezerwacje;

    /**
     * Seanse constructor.
     */
    public function __construct()
    {
        $this->seansMaFilmy = new ArrayCollection();
        $this->rezerwacje = new ArrayCollection();
        $this->tranzakcje = new ArrayCollection();
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
     * @return \DateTime|null
     */
    public function getPoczatekseansu(): ?\DateTime
    {
        return $this->poczatekseansu;
    }

    /**
     * @param \DateTime $poczatekseansu
     */
    public function setPoczatekseansu(\DateTime $poczatekseansu): void
    {
        $this->poczatekseansu = $poczatekseansu;
    }

    /**
     * @return bool|null
     */
    public function getCzyodwolany(): ?bool
    {
        return $this->czyodwolany;
    }

    /**
     * @param bool|null $czyodwolany
     */
    public function setCzyodwolany(?bool $czyodwolany): void
    {
        $this->czyodwolany = $czyodwolany;
    }

    /**
     * @return Pulebiletow|null
     */
    public function getPulebiletow(): ?Pulebiletow
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
     * @return Sale|null
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
     * @return Typyseansow|null
     */
    public function getTypyseansow(): ?Typyseansow
    {
        return $this->typyseansow;
    }

    /**
     * @param Typyseansow $typyseansow
     */
    public function setTypyseansow(Typyseansow $typyseansow): void
    {
        $this->typyseansow = $typyseansow;
    }

    /**
     * @return Wydarzeniaspecjalne|null
     */
    public function getWydarzeniaspecjalne(): ?Wydarzeniaspecjalne
    {
        return $this->wydarzeniaspecjalne;
    }

    /**
     * @param Wydarzeniaspecjalne|null $wydarzeniaspecjalne
     */
    public function setWydarzeniaspecjalne(?Wydarzeniaspecjalne $wydarzeniaspecjalne): void
    {
        $this->wydarzeniaspecjalne = $wydarzeniaspecjalne;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection|null
     */
    public function getSeansMaFilmy(): ?\Doctrine\Common\Collections\Collection
    {
        return $this->seansMaFilmy;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $seansMaFilmy
     */
    public function setSeansMaFilmy(\Doctrine\Common\Collections\Collection $seansMaFilmy): void
    {
        $this->seansMaFilmy = $seansMaFilmy;
    }

    public function getInitialCollectionsValues()
    {
        $smfs = $this->seansMaFilmy->getValues();
        $values = array();
        foreach($smfs AS $smf) {
            $values[$smf->getKolejnosc()] = $smf->getFilmy()->getId();
        }
        ksort($values);
        $string = implode('/', $values);
        if(substr($string, -1, 1) == '/') {
            $string = substr($string, 0, -1);
        }
        return $string;
    }

    public function getSeanceEndTime()
    {
        $Time = clone $this->poczatekseansu;

        foreach($this->seansMaFilmy as $key => $smf) {
            $Time->add(new \DateInterval('PT' . ($smf->getFilmy()->getCzastrwania()+$smf->getFilmy()->getCzasreklam()) . 'M'));
        }

        return $Time;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranzakcje(): \Doctrine\Common\Collections\Collection
    {
        return $this->tranzakcje;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $tranzakcje
     */
    public function setTranzakcje(\Doctrine\Common\Collections\Collection $tranzakcje): void
    {
        $this->tranzakcje = $tranzakcje;
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
}
