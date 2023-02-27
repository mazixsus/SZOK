<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tranzakcje
 *
 * @ORM\Table(name="tranzakcje", uniqueConstraints={@ORM\UniqueConstraint(name="idTranzakcje_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_Tranzakcje_RodzajePlatnosci1_idx", columns={"RodzajePlatnosci_id"}), @ORM\Index(name="fk_Tranzakcje_Uzytkownicy1_idx", columns={"Uzytkownicy_id"}), @ORM\Index(name="fk_Tranzakcje_Pracownicy1_idx", columns={"Pracownicy_id"}), @ORM\Index(name="fk_Tranzakcje_Promocje1_idx", columns={"Promocje_id"}), @ORM\Index(name="fk_Tranzakcje_Seanse1_idx", columns={"Seanse_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\TranzakcjeRepository")
 */
class Tranzakcje
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
     * @return \DateTime
     */
    public function getData(): \DateTime
    {
        return $this->data;
    }

    /**
     * @param \DateTime $data
     */
    public function setData(\DateTime $data): void
    {
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function getCzyodwiedzajacy(): int
    {
        return $this->czyodwiedzajacy;
    }

    /**
     * @param int $czyodwiedzajacy
     */
    public function setCzyodwiedzajacy(int $czyodwiedzajacy): void
    {
        $this->czyodwiedzajacy = $czyodwiedzajacy;
    }

    /**
     * @return Pracownicy|null
     */
    public function getPracownicy(): ?Pracownicy
    {
        return $this->pracownicy;
    }

    /**
     * @param Pracownicy|null $pracownicy
     */
    public function setPracownicy(?Pracownicy $pracownicy): void
    {
        $this->pracownicy = $pracownicy;
    }

    /**
     * @return ?Promocje
     */
    public function getPromocje(): ?Promocje
    {
        return $this->promocje;
    }

    /**
     * @param Promocje $promocje
     */
    public function setPromocje(?Promocje $promocje): void
    {
        $this->promocje = $promocje;
    }

    /**
     * @return Rodzajeplatnosci
     */
    public function getRodzajeplatnosci(): Rodzajeplatnosci
    {
        return $this->rodzajeplatnosci;
    }

    /**
     * @param Rodzajeplatnosci $rodzajeplatnosci
     */
    public function setRodzajeplatnosci(Rodzajeplatnosci $rodzajeplatnosci): void
    {
        $this->rodzajeplatnosci = $rodzajeplatnosci;
    }

    /**
     * @return \Seanse
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
     * @return Uzytkownicy|null
     */
    public function getUzytkownicy(): ?Uzytkownicy
    {
        return $this->uzytkownicy;
    }

    /**
     * @param Uzytkownicy|null $uzytkownicy
     */
    public function setUzytkownicy(?Uzytkownicy $uzytkownicy): void
    {
        $this->uzytkownicy = $uzytkownicy;
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
     * @var \DateTime
     *
     * @ORM\Column(name="data", type="datetime", nullable=false)
     */
    private $data;

    /**
     * @var int
     *
     * @ORM\Column(name="czyOdwiedzajacy", type="integer", nullable=false)
     */
    private $czyodwiedzajacy;

    /**
     * @var \Pracownicy
     *
     * @ORM\ManyToOne(targetEntity="Pracownicy")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Pracownicy_id", referencedColumnName="id")
     * })
     */
    private $pracownicy;

    /**
     * @var \Promocje
     *
     * @ORM\ManyToOne(targetEntity="Promocje")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Promocje_id", referencedColumnName="id")
     * })
     */
    private $promocje;

    /**
     * @var \Rodzajeplatnosci
     *
     * @ORM\ManyToOne(targetEntity="Rodzajeplatnosci")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="RodzajePlatnosci_id", referencedColumnName="id")
     * })
     */
    private $rodzajeplatnosci;

    /**
     * @var \Seanse
     *
     * @ORM\ManyToOne(targetEntity="Seanse", inversedBy="tranzakcje")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Seanse_id", referencedColumnName="id")
     * })
     */
    private $seanse;

    /**
     * @var \Uzytkownicy
     *
     * @ORM\ManyToOne(targetEntity="Uzytkownicy")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Uzytkownicy_id", referencedColumnName="id")
     * })
     */
    private $uzytkownicy;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Bilety", mappedBy="tranzakcje", cascade={"persist"})
     */
    private $bilety;

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

    public function getSum()
    {
        $sum=0.00;
        foreach ($this->bilety->getIterator() as $ticket)
        {
            $sum+=$ticket->getCena();
        }
        return $sum;
    }
}
