<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sale
 *
 * @ORM\Table(name="sale", uniqueConstraints={@ORM\UniqueConstraint(name="idSale_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="numerSali_UNIQUE", columns={"numerSali"})})
 * @ORM\Entity(repositoryClass="App\Repository\SaleRepository")
 */
class Sale
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
    public function getNumersali(): string
    {
        return $this->numersali;
    }

    /**
     * @param string $numersali
     */
    public function setNumersali(string $numersali): void
    {
        $this->numersali = $numersali;
    }

    /**
     * @return int
     */
    public function getDlugoscsali(): int
    {
        return $this->dlugoscsali;
    }

    /**
     * @param int $dlugoscsali
     */
    public function setDlugoscsali(int $dlugoscsali): void
    {
        $this->dlugoscsali = $dlugoscsali;
    }

    /**
     * @return int
     */
    public function getSzerokoscsali(): int
    {
        return $this->szerokoscsali;
    }

    /**
     * @param int $szerokoscsali
     */
    public function setSzerokoscsali(int $szerokoscsali): void
    {
        $this->szerokoscsali = $szerokoscsali;
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
     * @ORM\Column(name="numerSali", type="string", length=3, nullable=false)
     */
    private $numersali;

    /**
     * @var int
     *
     * @ORM\Column(name="dlugoscSali", type="integer", nullable=false)
     */
    private $dlugoscsali;

    /**
     * @var int
     *
     * @ORM\Column(name="szerokoscSali", type="integer", nullable=false)
     */
    private $szerokoscsali;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Rzedy", mappedBy="sale")
     */
    private $rzedy;

    public function __toString()
    {
        return 'Sala '.$this->numersali;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRzedy(): \Doctrine\Common\Collections\Collection
    {
        return $this->rzedy;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $rzedy
     */
    public function setRzedy(\Doctrine\Common\Collections\Collection $rzedy): void
    {
        $this->rzedy = $rzedy;
    }
}
