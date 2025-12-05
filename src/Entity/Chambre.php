<?php

namespace App\Entity;

use App\Repository\ChambreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChambreRepository::class)]
class Chambre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $number = null;

    #[ORM\Column]
    private ?int $floor = null;

    #[ORM\Column]
    private ?float $area = null;

    #[ORM\Column]
    private ?float $pricePerNight = null;

    #[ORM\Column]
    private ?bool $isAvailable = null;

    #[ORM\ManyToOne]
    private ?Hotel $hotel = null;

    #[ORM\ManyToOne(inversedBy: 'chambres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClassementH $Classement = null;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\ManyToMany(targetEntity: Service::class, inversedBy: 'chambres')]
    private Collection $services;

    /**
     * @var Collection<int, Travaux>
     */
    #[ORM\OneToMany(targetEntity: Travaux::class, mappedBy: 'chambre')]
    private Collection $travaux;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->travaux = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getFloor(): ?int
    {
        return $this->floor;
    }

    public function setFloor(int $floor): static
    {
        $this->floor = $floor;

        return $this;
    }

    public function getArea(): ?float
    {
        return $this->area;
    }

    public function setArea(float $area): static
    {
        $this->area = $area;

        return $this;
    }

    public function getPricePerNight(): ?float
    {
        return $this->pricePerNight;
    }

    public function setPricePerNight(float $pricePerNight): static
    {
        $this->pricePerNight = $pricePerNight;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function setHotel(?Hotel $hotel): static
    {
        $this->hotel = $hotel;

        return $this;
    }

    public function getClassement(): ?ClassementH
    {
        return $this->Classement;
    }

    public function setClassement(?ClassementH $Classement): static
    {
        $this->Classement = $Classement;

        return $this;
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): static
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        $this->services->removeElement($service);

        return $this;
    }

    /**
     * @return Collection<int, Travaux>
     */
    public function getTravaux(): Collection
    {
        return $this->travaux;
    }

    public function addTravaux(Travaux $travaux): static
    {
        if (!$this->travaux->contains($travaux)) {
            $this->travaux->add($travaux);
            $travaux->setChambre($this);
        }

        return $this;
    }

    public function removeTravaux(Travaux $travaux): static
    {
        if ($this->travaux->removeElement($travaux)) {
            // set the owning side to null (unless already changed)
            if ($travaux->getChambre() === $this) {
                $travaux->setChambre(null);
            }
        }

        return $this;
    }
}
