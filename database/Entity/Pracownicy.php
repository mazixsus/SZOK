<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pracownicy
 *
 * @ORM\Table(name="pracownicy", uniqueConstraints={@ORM\UniqueConstraint(name="idPracownicy_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="login_UNIQUE", columns={"login"}), @ORM\UniqueConstraint(name="email_UNIQUE", columns={"email"}), @ORM\UniqueConstraint(name="telefon_UNIQUE", columns={"telefon"})}, indexes={@ORM\Index(name="fk_Pracownicy_Role1_idx", columns={"Role_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\PracownicyRepository")
 *
 * @UniqueEntity(
 *     fields={"login"},
 *     errorPath="login",
 *     message="Ten login jest już w użyciu"
 * )
 * * @UniqueEntity(
 *     fields={"email"},
 *     errorPath="email",
 *     message="Ten email jest już w użyciu"
 * )
 * * @UniqueEntity(
 *     fields={"telefon"},
 *     errorPath="telefon",
 *     message="Ten telefon jest już w użyciu"
 * )
 */


class Pracownicy implements UserInterface
{
    /**
     * @return bool|null
     */
    public function getCzyaktywny(): ?bool
    {
        return $this->czyaktywny;
    }

    /**
     * @param bool|null $czyaktywny
     */
    public function setCzyaktywny(?bool $czyaktywny): void
    {
        $this->czyaktywny = $czyaktywny;
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
     * @return string
     */
    public function getHaslo(): ?string
    {
        return $this->haslo;
    }

    /**
     * @param string $haslo
     */
    public function setHaslo(string $haslo): void
    {
        $this->haslo = $haslo;
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
     * @return string
     */
    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login): void
    {
        $this->login = $login;
    }

    /**
     * @return \Role
     */
    public function getRole(): ?Role
    {
        return $this->role;
    }

    /**
     * @param \Role $role
     */
    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="login", type="string", length=50, nullable=false)
     *
     * @Assert\Regex(
     *     pattern="/^[\p{L}\d\-\_]+$/u",
     *     message="Wymagane od 5 do 45 dużych/małych liter
     *              lub cyfr(dozwolone \'-\' oraz \'_\')."
     * )
     * @Assert\Length(
     *     max = 45,
     *     min = 5,
     *     maxMessage = "Login może zawierać maksymalnie 45 znaków.",
     *     minMessage = "Login musi zawierać minimum 5 znaków."
     * )
     */

    private $login;

    /**
     * @var \Role
     *
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Role_id", referencedColumnName="id")
     * })
     */
    private $role;

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
     * @ORM\Column(name="haslo", type="string", length=64, nullable=false)
     * @Assert\Regex(
     *     pattern="/^[\S]+$/u",
     *     message="Hasło może składać się ze wszystkich znaków z wyłączeniem znaków białych"
     * )
     * @Assert\Length(
     *     max = 64,
     *     min = 8,
     *     maxMessage = "Hasło może zawierać maksymalnie 64 znaków.",
     *     minMessage = "Hasło musi zawierać minimum 8 znaków."
     * )
     *
     */
    private $haslo;

    /**
     * @var string
     *
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
     *
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
     *
     */
    private $nazwisko;

    /**
     * @var string
     *
     * @ORM\Column(name="telefon", type="string", length=9, nullable=false)
     *
     * * @Assert\Regex(
     *     pattern="/^\d{9}$/u",
     *     message="Numer telefonu powinien składac się z 9 cyfr."
     * )
     */
    private $telefon;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     *

     * @Assert\Length(
     *     max = 255,
     *     min = 5,
     *     maxMessage = "Email może zawierać maksymalnie 255 znaków.",
     *     minMessage = "Email musi zawierać minimum 5 znaków."
     * )
     *
     *  /**
     * @Assert\Email(
     *     message = "Ten mail nie jest poprawny.",
     *     mode="loose"
     * )
     */
    private $email;

    /**
     * @var \bool|null
     *
     * @ORM\Column(name="czyAktywny", type="boolean", nullable=true)
     */
    private $czyaktywny;




    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return array('ROLE_USER');
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        switch ($this->role->getId()) {
            case 3 :
                return [
                    'ROLE_WORKER'
                ];
            case 2 :
                return [
                    'ROLE_MANAGER'
                ];
            case 1 :
                return [
                    'ROLE_ADMIN'
                ];
            default:
                return [
                    'ROLE_WORKER'
                ];
        }
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->haslo;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        $this->login;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    public function __toString()
    {
        return ''. $this->imie . ' ' . $this->nazwisko . ' (' . $this->role . ')';
    }
}

