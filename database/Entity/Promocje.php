<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Promocje
 *
 * @ORM\Table(name="promocje", uniqueConstraints={@ORM\UniqueConstraint(name="idPromocje_UNIQUE", columns={"id"})})
 * @ORM\Entity(repositoryClass="App\Repository\PromocjeRepository")
 */
class Promocje
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
     * @return bool
     */
    public function isCzykwotowa(): ?bool
    {
        return $this->czykwotowa;
    }

    /**
     * @param bool $czykwotowa
     */
    public function setCzykwotowa(bool $czykwotowa): void
    {
        $this->czykwotowa = $czykwotowa;
    }

    /**
     * @return string
     */
    public function getWartosc(): ?string
    {
        return $this->wartosc;
    }

    /**
     * @param string $wartosc
     */
    public function setWartosc(string $wartosc): void
    {
        $this->wartosc = $wartosc;
    }

    /**
     * @return \DateTime
     */
    public function getPoczatekpromocji(): ?\DateTime
    {
        return $this->poczatekpromocji;
    }

    /**
     * @param \DateTime $poczatekpromocji
     */
    public function setPoczatekpromocji(\DateTime $poczatekpromocji): void
    {
        $this->poczatekpromocji = $poczatekpromocji;
    }

    /**
     * @return \DateTime
     */
    public function getKoniecpromocji(): ?\DateTime
    {
        return $this->koniecpromocji;
    }

    /**
     * @param \DateTime $koniecpromocji
     */
    public function setKoniecpromocji(\DateTime $koniecpromocji): void
    {
        $this->koniecpromocji = $koniecpromocji;
    }

    /**
     * @return bool|null
     */
    public function getCzykobieta(): ?bool
    {
        return $this->czykobieta;
    }

    /**
     * @param bool|null $czykobieta
     */
    public function setCzykobieta(?bool $czykobieta): void
    {
        $this->czykobieta = $czykobieta;
    }

    /**
     * @return \DateTime|null
     */
    public function getStaz(): ?\DateTime
    {
        return $this->staz;
    }

    /**
     * @param \DateTime|null $staz
     */
    public function setStaz(?\DateTime $staz): void
    {
        $this->staz = $staz;
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
     * @ORM\Column(name="nazwa", type="string", length=45, nullable=false)
     * @Assert\Regex(
     *     pattern="/^[\p{L}\d\s\-]+$/u",
     *     message="Nazwa powinna się składać tylko z liter, spacji, myślników i cyfr."
     * )
     * @Assert\Length(
     *     max = 45,
     *     min = 5,
     *     maxMessage = "Nazwa może zawierać maksymalnie 45 znaków.",
     *     minMessage = "Nazwa musi zawierać minimum 5 znaków."
     * )
     */
    private $nazwa;

    /**
     * @var bool
     *
     * @ORM\Column(name="czyKwotowa", type="boolean", nullable=false)
     */
    private $czykwotowa;

    /**
     * @var string
     *
     * @ORM\Column(name="wartosc", type="decimal", precision=5, scale=2, nullable=false)
     * @Assert\Range(
     *     min="0.01",
     *     max="100.00",
     *     minMessage="Wartość promocji musi być większa od zera.",
     *     maxMessage="Wartość promocji nie powinna przekraczać 100.00."
     * )
     */
    private $wartosc;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="poczatekPromocji", type="date", nullable=false)
     * @Assert\GreaterThan(
     *     value="today",
     *     message="Promocja powinna rozpoczynać się najwcześniej w dniu jutrzejszym."
     *     )
     */
    private $poczatekpromocji;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="koniecPromocji", type="date", nullable=false)
     * @Assert\Expression(
     *     expression="value >= this.getPoczatekpromocji()",
     *     message="Koniec promocji nie może być wcześniej niż jej początek."
     * )
     */
    private $koniecpromocji;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="czyKobieta", type="boolean", nullable=true)
     */
    private $czykobieta;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="staz", type="date", nullable=true)
     * @Assert\Expression(
     *     expression="value <= this.getPoczatekpromocji() or value == null",
     *     message="Staż powinien wskazywać co najmniej początek promocji lub wcześniej."
     * )
     */
    private $staz;


}
