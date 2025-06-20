<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $orderDate = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?string $carrierId = null;

    #[ORM\Column]
    private ?string $paymentId = null;

    #[ORM\Column]
    private ?float $orderTotal = null;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(targetEntity: OrderItem::class, mappedBy: 'orderId')]
    private Collection $orderItems;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Customer $customer = null;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderDate(): ?\DateTimeImmutable
    {
        return $this->orderDate;
    }

    public function setOrderDate(\DateTimeImmutable $orderDate): static
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCarrierId(): ?string
    {
        return $this->carrierId;
    }

    public function setCarrierId(string $carrierId): static
    {
        $this->carrierId = $carrierId;

        return $this;
    }

    public function getPaymentId(): ?string
    {
        return $this->paymentId;
    }

    public function setPaymentId(string $paymentId): static
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    public function getOrderTotal(): ?float
    {
        return $this->orderTotal;
    }

    public function setOrderTotal(float $orderTotal): static
    {
        $this->orderTotal = $orderTotal;

        return $this;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(OrderItem $orderItem): static
    {
        if (! $this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setOrderId($this);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getOrderId() === $this) {
                $orderItem->setOrderId(null);
            }
        }

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }
}
