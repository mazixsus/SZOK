<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Filmy
 *
 * @ORM\Table(name="filmy", uniqueConstraints={@ORM\UniqueConstraint(name="idFilmy_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_Filmy_KategorieWiekowe1_idx", columns={"KategorieWiekowe_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\FilmyRepository")
 */
class Filmy
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
    public function getTytul(): ?string
    {
        return $this->tytul;
    }

    /**
     * @param string $tytul
     */
    public function setTytul(string $tytul): void
    {
        $this->tytul = $tytul;
    }

    /**
     * @return null|string
     */
    public function getOpis(): ?string
    {
        return $this->opis;
    }

    /**
     * @param null|string $opis
     */
    public function setOpis(?string $opis): void
    {
        $this->opis = $opis;
    }

    /**
     * @return \DateTime
     */
    public function getDatapremiery(): ?\DateTime
    {
        return $this->datapremiery;
    }

    /**
     * @param \DateTime $datapremiery
     */
    public function setDatapremiery(\DateTime $datapremiery): void
    {
        $this->datapremiery = $datapremiery;
    }

    /**
     * @return int
     */
    public function getCzastrwania(): ?int
    {
        return $this->czastrwania;
    }

    /**
     * @param int $czastrwania
     */
    public function setCzastrwania(int $czastrwania): void
    {
        $this->czastrwania = $czastrwania;
    }

    /**
     * @return int
     */
    public function getCzasreklam(): ?int
    {
        return $this->czasreklam;
    }

    /**
     * @param int $czasreklam
     */
    public function setCzasreklam(int $czasreklam): void
    {
        $this->czasreklam = $czasreklam;
    }

    /**
     * @return null|string
     */
    public function getPlakat(): ?string
    {
        return $this->plakat;
    }

    /**
     * @param null|string $plakat
     */
    public function setPlakat(?string $plakat): void
    {
        $this->plakat = $plakat;
    }

    /**
     * @return null|string
     */
    public function getZwiastun(): ?string
    {
        return $this->zwiastun;
    }

    /**
     * @param null|string $zwiastun
     */
    public function setZwiastun(?string $zwiastun): void
    {
        $this->zwiastun = $zwiastun;
    }

    /**
     * @return Kategoriewiekowe|null
     */
    public function getKategoriewiekowe(): ?Kategoriewiekowe
    {
        return $this->kategoriewiekowe;
    }

    /**
     * @param Kategoriewiekowe $kategoriewiekowe
     */
    public function setKategoriewiekowe(Kategoriewiekowe $kategoriewiekowe): void
    {
        $this->kategoriewiekowe = $kategoriewiekowe;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRodzajefilmow(): \Doctrine\Common\Collections\Collection
    {
        return $this->rodzajefilmow;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $rodzajefilmow
     */
    public function setRodzajefilmow(\Doctrine\Common\Collections\Collection $rodzajefilmow): void
    {
        $this->rodzajefilmow = $rodzajefilmow;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTypyseansow(): \Doctrine\Common\Collections\Collection
    {
        return $this->typyseansow;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $typyseansow
     */
    public function setTypyseansow(\Doctrine\Common\Collections\Collection $typyseansow): void
    {
        $this->typyseansow = $typyseansow;
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
     * @ORM\Column(name="tytul", type="string", length=127, nullable=false)
     * @Assert\Regex(
     *     pattern="/^[^\<\>]+$/u",
     *     message="Tytuł nie może zawierać następujących znaków: '<', '>'."
     * )
     * @Assert\Length(
     *     max = 127,
     *     min = 2,
     *     maxMessage = "Tytuł może zawierać maksymalnie 127 znaków.",
     *     minMessage = "Tytuł musi zawierać minimum 2 znaków."
     * )
     * @Assert\NotBlank(
     *     message="Tytuł nie może być pusty."
     * )
     */
    private $tytul;

    /**
     * @var string|null
     *
     * @ORM\Column(name="opis", type="string", length=512, nullable=true)
     * @Assert\Length(
     *     max = 512,
     *     maxMessage = "Opis może zawierać maksymalnie 512 znaków."
     * )
     */
    private $opis;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dataPremiery", type="date", nullable=false)
     * @Assert\NotBlank(
     *     message="Data premiery nie może być pusta."
     * )
     * @Assert\Date(
     *     message="Podana wartość nie jest datą"
     * )
     */
    private $datapremiery;

    /**
     * @var int
     *
     * @ORM\Column(name="czasTrwania", type="integer", nullable=false)
     * @Assert\GreaterThan(
     *     value="0",
     *     message="Czas trwania musi być większy od 0"
     * )
     * @Assert\LessThan(
     *     value="720",
     *     message="Długość filmu nie może przekraczać 720 minut."
     * )
     */
    private $czastrwania;

    /**
     * @var int
     *
     * @ORM\Column(name="czasReklam", type="integer", nullable=false)
     * @Assert\GreaterThanOrEqual(
     *     value="0",
     *     message="Czas reklam mnie może być ujemny."
     * )
     * @Assert\LessThan(
     *     value="30",
     *     message="Czas reklam nie może przekraczać 30 minut."
     * )
     */
    private $czasreklam;

    /**
     * @var string|null
     *
     * @ORM\Column(name="plakat", type="string", length=255, nullable=true)
     * @Assert\Image(
     *     mimeTypes={"image/jpeg", "image/png", "image/jpg"},
     *     mimeTypesMessage="Załączony plik nie jest obrazem.",
     *     maxSize="1000K",
     *     maxSizeMessage="Twój plik przekracza dopuszczalny rozmiar 1000KB."
     * )
     */
    private $plakat;

    /**
     * @var string|null
     *
     * @ORM\Column(name="zwiastun", type="string", length=255, nullable=true)
     * @Assert\Length(
     *     max="255",
     *     maxMessage="Link do zwiastuna może zawierać maksymalnie 255 znaków."
     * )
     */
    private $zwiastun;

    /**
     * @var Kategoriewiekowe
     *
     * @ORM\ManyToOne(targetEntity="Kategoriewiekowe")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="KategorieWiekowe_id", referencedColumnName="id")
     * })
     * @Assert\NotBlank(
     *     message="Kategoria wiekowa jest wymagana"
     * )
     */
    private $kategoriewiekowe;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Rodzajefilmow", inversedBy="filmy")
     * @ORM\JoinTable(name="film_ma_rodzajefilmow",
     *   joinColumns={
     *     @ORM\JoinColumn(name="Filmy_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="RodzajeFilmow_id", referencedColumnName="id")
     *   }
     * )
     * @Assert\Count(
     *     min="1",
     *     minMessage="Musisz wybrać przynajmniej jeden gatunek filmowy."
     * )
     */
    private $rodzajefilmow;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="Typyseansow", inversedBy="filmy")
     * @ORM\JoinTable(name="film_ma_typyseansow",
     *   joinColumns={
     *     @ORM\JoinColumn(name="Filmy_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="TypySeansow_id", referencedColumnName="id")
     *   }
     * )
     * @Assert\Count(
     *     min="1",
     *     minMessage="Musisz wybrać przynajmniej jeden format filmu"
     * )
     */
    private $typyseansow;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rodzajefilmow = new \Doctrine\Common\Collections\ArrayCollection();
        $this->typyseansow = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="SeansMaFilmy", mappedBy="filmy")
     */
    private $seansMaFilmy;

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSeansMaFilmy(): \Doctrine\Common\Collections\Collection
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

    public function __toString()
    {
        return $this->tytul . ' (' . $this->datapremiery->format('Y') . ')';
    }
}
