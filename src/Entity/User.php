<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

// Aquesta és la meva entitat principal per l'usuari. Doctrine em crearà una taula anomenada "user".
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
// M'asseguro que l'email sigui completament únic abans de desar-lo a la BDD amb Missatges de Fallida validats
#[UniqueEntity(fields: ['email'], message: 'Ja existeix un compte amb aquest correu electrònic')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Aquest és la clau principal i auto numèrica
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // L'email amb un màxim estandar normaltzat de 180 per symfony.
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    // Configuro el llistat sencer de privilegis tipus json JSON
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string Aquesta és la contrasenya ja encriptada de maneres segures que em guardarà.
     */
    #[ORM\Column]
    private ?string $password = null;

    // Afegeixo el camp del nom a l'igual que un usuari personalitzat amb string
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    // Enllaço aquesta clau ManyToOne entre l'usuari amb productes seus (Cascade eliminable).
    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Product::class, cascade: ['persist', 'remove'])]
    private Collection $products;

    // Preparo el constructor de la class inicial d'aquesta llista que per defecte és de tipo Array per symfony.
    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * És la meva definició personal on l'email és "L'Identificador"
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Comprovo i dono sempre a tothom la marca basica normalitzada d'Usuari normal "ROLE_USER" com requereixo d'estandard 
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Amb això retorno la llista complerta
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    // Puc sumar un producte assignant-m'hi
    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setOwner($this);
        }

        return $this;
    }

    // Així es treu ràpid el laç o vincle d'l'objecte 
    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getOwner() === $this) {
                $product->setOwner(null);
            }
        }

        return $this;
    }

    // Aquesta serialització evito per simplicitza dades sense el text pla
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password ?? '');
        
        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }
}
