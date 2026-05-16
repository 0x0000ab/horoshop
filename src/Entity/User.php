<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['login'], message: 'Already in use')]
#[UniqueEntity(fields: ['pass'], message: 'Already in use')]
#[ORM\Index(columns: ['login', 'pass'], name: "idx_auth")]
#[ORM\Index(columns: ['access_token'], name: "idx_at")]
#[ORM\Table(name: 'users')]
class User implements UserInterface
{
    const ROLE_ROOT = 'ROLE_ROOT';
    const ROLE_USER = 'ROLE_USER';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(
        type: 'bigint',
        options: ['unsigned' => true]
    )]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 8)]
    #[ORM\Column(length: 8, unique: true)]
    private ?string $login = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 8)]
    #[ORM\Column(length: 8)]
    private ?string $phone = null;

    #[Assert\Length(min: 3, max: 8)]
    #[ORM\Column(length: 8, unique: true)]
    private ?string $pass = null;

    #[ORM\Column(length: 255)]
    private ?string $accessToken = null;

    #[ORM\Column]
    private array $roles = [];

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->id;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setPass(string $pass): static
    {
        $this->pass = $pass;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken()
    {
        $this->accessToken = sha1($this->login . $this->pass . uniqid(mt_rand(), true));
        return $this;
    }

    public function load($data): void
    {
        $login = $data['login'] ?? null;
        if ($login) {
            $this->setLogin($login);
        }

        $phone = $data['phone'] ?? null;
        if ($phone) {
            $this->setPhone($phone);
        }

        $pass = $data['pass'] ?? null;
        if ($pass) {
            $this->setPass($pass);
        }
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'login' => $this->login,
            'phone' => $this->phone,
            'pass' => $this->pass,
            'accessToken' => $this->accessToken,
        ];
    }

}
