<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Rezerwacje
 *
 * @ORM\Table(name="rezerwacje", uniqueConstraints={@ORM\UniqueConstraint(name="idRezerwacje_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_Rezerwacje_Uzytkownicy1_idx", columns={"Uzytkownicy_id"}), @ORM\Index(name="fk_Rezerwacje_Pracownicy1_idx", columns={"Pracownicy_id"}), @ORM\Index(name="fk_Rezerwacje_Seanse1_idx", columns={"Seanse_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\RezerwacjeRepository")
 */
class Rezerwacje
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
    public function getImie(): ?string
    {
        return $this->imie;
    }

    /**
     * @param string $imie
     */
    public function setImie(string $imie): void
    {
        $this->imie = $imie;
    }

    /**
     * @return string
     */
    public function getNazwisko(): ?string
    {
        return $this->nazwisko;
    }

    /**
     * @param string $nazwisko
     */
    public function setNazwisko(string $nazwisko): void
    {
        $this->nazwisko = $nazwisko;
    }

    /**
     * @return string
     */
    public function getTelefon(): ?string
    {
        return $this->telefon;
    }

    /**
     * @param string $telefon
     */
    public function setTelefon(string $telefon): void
    {
        $this->telefon = $telefon;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return bool
     */
    public function isCzyodwiedzajacy(): bool
    {
        return $this->czyodwiedzajacy;
    }

    /**
     * @param bool $czyodwiedzajacy
     */
    public function setCzyodwiedzajacy(bool $czyodwiedzajacy): void
    {
        $this->czyodwiedzajacy = $czyodwiedzajacy;
    }

    /**
     * @return bool
     */
    public function isSfinalizowana(): bool
    {
        return $this->sfinalizowana;
    }

    /**
     * @param bool $sfinalizowana
     */
    public function setSfinalizowana(bool $sfinalizowana): void
    {
        $this->sfinalizowana = $sfinalizowana;
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
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMiejsca(): \Doctrine\Common\Collections\Collection
    {
        return $this->miejsca;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $miejsca
     */
    public function setMiejsca(\Doctrine\Common\Collections\ArrayCollection $miejsca): void
    {
        $this->miejsca = $miejsca;
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
     * @ORM\Column(name="imie", type="string", length=45, nullable=false)
     * @Assert\Regex(
     *     pattern="/^\p{Lu}[\p{L}\s]+$/u",
     *     message="Imię powinno składać się tylko z liter lub spacji i rozpoczynać się wielką literą."
     * )
     * @Assert\Length(
     *     max = 45,
     *     min = 3,
     *     maxMessage = "Imię może zawierać maksymalnie 45 znaków.",
     *     minMessage = "Imię musi zawierać minimum 3 znaki."
     * )
     */
    private $imie;

    /**
     * @var string
     *
     * @ORM\Column(name="nazwisko", type="string", length=45, nullable=false)
     * @Assert\Regex(
     *     pattern="/^\p{Lu}[\p{L}\d\s\-]+$/u",
     *     message="Nazwisko powinno się składać tylko z liter, spacji oraz myślników i zaczynać się wielką literą."
     * )
     * @Assert\Length(
     *     max = 45,
     *     min = 2,
     *     maxMessage = "Nazwisko może zawierać maksymalnie 45 znaków.",
     *     minMessage = "Nazwisko musi zawierać minimum 2 znaki."
     * )
     */
    private $nazwisko;

    /**
     * @var string
     * @ORM\Column(name="telefon", type="string", length=9, nullable=false)
     * @Assert\Regex(
     *     pattern="/^\d{9}$/u",
     *     message="Numer telefonu powinien składac się z 9 cyfr."
     * )
     */
    private $telefon;

    /**
     * @var string
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     * @Assert\Length(
     *     max = 255,
     *     min = 5,
     *     maxMessage = "Email może zawierać maksymalnie 255 znaków.",
     *     minMessage = "Email musi zawierać minimum 5 znaków."
     * )
     * @Assert\Email(
     *     message = "Ten mail nie jest poprawny.",
     *     mode="loose"
     * )
     */
    private $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="czyOdwiedzajacy", type="boolean", nullable=false)
     */
    private $czyodwiedzajacy;

    /**
     * @var bool
     *
     * @ORM\Column(name="sfinalizowana", type="boolean", nullable=false)
     */
    private $sfinalizowana;

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
     * @var \Seanse
     *
     * @ORM\ManyToOne(targetEntity="Seanse", inversedBy="rezerwacje")
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
     * @ORM\ManyToMany(targetEntity="Miejsca", inversedBy="rezerwacje")
     * @ORM\JoinTable(name="rezerwacja_ma_miejsca",
     *   joinColumns={
     *     @ORM\JoinColumn(name="Rezerwacje_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="Miejsca_id", referencedColumnName="id")
     *   }
     * )
     */
    private $miejsca;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->miejsca = new \Doctrine\Common\Collections\ArrayCollection();
    }

}
