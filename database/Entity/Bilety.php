<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Bilety
 *
 * @ORM\Table(name="bilety", uniqueConstraints={@ORM\UniqueConstraint(name="Biletcol_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_Tranzakcje_has_RodzajeBiletow_RodzajeBiletow1_idx", columns={"RodzajeBiletow_id"}), @ORM\Index(name="fk_Tranzakcje_has_RodzajeBiletow_Tranzakcje1_idx", columns={"Tranzakcje_id"}), @ORM\Index(name="fk_Tranzakcja_ma_Bilet_Miejsca1_idx", columns={"Miejsca_id"}), @ORM\Index(name="fk_Bilety_Vouchery1_idx", columns={"Vouchery_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\BiletyRepository")
 */
class Bilety
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
     * @return string
     */
    public function getLosowecyfry(): string
    {
        return $this->losowecyfry;
    }

    /**
     * @param string $losowecyfry
     */
    public function setLosowecyfry(string $losowecyfry): void
    {
        $this->losowecyfry = $losowecyfry;
    }

    /**
     * @return string
     */
    public function getCyfrakontrolna(): string
    {
        return $this->cyfrakontrolna;
    }

    /**
     * @param string $cyfrakontrolna
     */
    public function setCyfrakontrolna(string $cyfrakontrolna): void
    {
        $this->cyfrakontrolna = $cyfrakontrolna;
    }

    /**
     * @return bool|null
     */
    public function getCzywykorzystany(): ?bool
    {
        return $this->czywykorzystany;
    }

    /**
     * @param bool|null $czywykorzystany
     */
    public function setCzywykorzystany(?bool $czywykorzystany): void
    {
        $this->czywykorzystany = $czywykorzystany;
    }

    /**
     * @return bool|null
     */
    public function getCzyanulowany(): ?bool
    {
        return $this->czyanulowany;
    }

    /**
     * @param bool|null $czyanulowany
     */
    public function setCzyanulowany(?bool $czyanulowany): void
    {
        $this->czyanulowany = $czyanulowany;
    }

    /**
     * @return \Vouchery
     */
    public function getVouchery(): Vouchery
    {
        return $this->vouchery;
    }

    /**
     * @param ?Vouchery $vouchery
     */
    public function setVouchery(?Vouchery $vouchery): void
    {
        $this->vouchery = $vouchery;
    }

    /**
     * @return \Miejsca
     */
    public function getMiejsca(): Miejsca
    {
        return $this->miejsca;
    }

    /**
     * @param Miejsca $miejsca
     */
    public function setMiejsca(Miejsca $miejsca): void
    {
        $this->miejsca = $miejsca;
    }

    /**
     * @return \Rodzajebiletow
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
     * @return \Tranzakcje
     */
    public function getTranzakcje(): Tranzakcje
    {
        return $this->tranzakcje;
    }

    /**
     * @param Tranzakcje $tranzakcje
     */
    public function setTranzakcje(Tranzakcje $tranzakcje): void
    {
        $this->tranzakcje = $tranzakcje;
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
     * @var string
     *
     * @ORM\Column(name="losoweCyfry", type="decimal", precision=3, scale=0, nullable=false)
     */
    private $losowecyfry;

    /**
     * @var string
     *
     * @ORM\Column(name="cyfraKontrolna", type="decimal", precision=1, scale=0, nullable=false)
     */
    private $cyfrakontrolna;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="czyWykorzystany", type="boolean", nullable=true)
     */
    private $czywykorzystany;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="czyAnulowany", type="boolean", nullable=true)
     */
    private $czyanulowany;

    /**
     * @var \Vouchery
     *
     * @ORM\ManyToOne(targetEntity="Vouchery")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Vouchery_id", referencedColumnName="id")
     * })
     */
    private $vouchery;

    /**
     * @var \Miejsca
     *
     * @ORM\ManyToOne(targetEntity="Miejsca", inversedBy="bilety")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Miejsca_id", referencedColumnName="id")
     * })
     */
    private $miejsca;

    /**
     * @var \Rodzajebiletow
     *
     * @ORM\ManyToOne(targetEntity="Rodzajebiletow", inversedBy="bilety")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="RodzajeBiletow_id", referencedColumnName="id")
     * })
     */
    private $rodzajebiletow;

    /**
     * @var \Tranzakcje
     *
     * @ORM\ManyToOne(targetEntity="Tranzakcje", inversedBy="bilety")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Tranzakcje_id", referencedColumnName="id")
     * })
     */
    private $tranzakcje;

    public function recalculateControlDigit()
    {
        $code = "" . $this->tranzakcje->getData()->format("YmdHis") . sprintf("%03d", $this->losowecyfry) . sprintf("%010d", $this->id);
        $sum = 0;
        for($i = 0; $i < strlen($code); $i++) {
            $sum += (int)$code[$i] * ($i % 3 * 3 + 1);
        }
        if($sum % 10 == 0) $this->cyfrakontrolna = 0;
        else $this->cyfrakontrolna = 10 - $sum % 10;
    }

    public function getCode()
    {
        return "" . $this->tranzakcje->getData()->format("YmdHis") . sprintf("%03d", $this->losowecyfry) . sprintf("%010d", $this->id)
            . $this->cyfrakontrolna;
    }

    public static function verifyCode(string $code)
    {
        $controlDigitFromCode = substr($code, -1);
        $code = substr($code, 0, -1);

        $sum = 0;
        for($i = 0; $i < strlen($code); $i++) {
            $sum += (int)$code[$i] * ($i % 3 * 3 + 1);
        }

        if($sum % 10 == 0) $controlDigit = 0;
        else $controlDigit = 10 - $sum % 10;

        if($controlDigit == $controlDigitFromCode) return true;
        else return false;
    }
}
