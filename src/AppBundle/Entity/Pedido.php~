<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Pedido
 *
 * @ORM\Table(name="pedido")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PedidoRepository")
 */
class Pedido
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="codigo", type="string", length=255)
     */
    private $codigo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fechaRecepcion", type="datetime")
     */
    private $fechaRecepcion;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="fechaEntrega", type="datetime", nullable=true)
     */
    private $fechaEntrega;

    /**
     * @var string
     *
     * @ORM\Column(name="listaSocios", type="string", length=255, nullable=true)
     */
    private $listaSocios;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     *
     * @return Pedido
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set fechaRecepcion
     *
     * @param \DateTime $fechaRecepcion
     *
     * @return Pedido
     */
    public function setFechaRecepcion($fechaRecepcion)
    {
        $this->fechaRecepcion = $fechaRecepcion;

        return $this;
    }

    /**
     * Get fechaRecepcion
     *
     * @return \DateTime
     */
    public function getFechaRecepcion()
    {
        return $this->fechaRecepcion;
    }

    /**
     * Set fechaEntrega
     *
     * @param \DateTime $fechaEntrega
     *
     * @return Pedido
     */
    public function setFechaEntrega($fechaEntrega)
    {
        $this->fechaEntrega = $fechaEntrega;

        return $this;
    }

    /**
     * Get fechaEntrega
     *
     * @return \DateTime
     */
    public function getFechaEntrega()
    {
        return $this->fechaEntrega;
    }

    /**
     * Set listaSocios
     *
     * @param string $listaSocios
     *
     * @return Pedido
     */
    public function setListaSocios($listaSocios)
    {
        $this->listaSocios = $listaSocios;

        return $this;
    }

    /**
     * Get listaSocios
     *
     * @return string
     */
    public function getListaSocios()
    {
        return $this->listaSocios;
    }
    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="pedido")
     * @ORM\JoinColumn(name="sede_id", referencedColumnName="id")
     */
    private $user;

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Pedido
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @ORM\OneToMany(targetEntity="Socio", mappedBy="pedido")
     */
    private $socio;

    public function __construct()
    {
        $this->socio= new ArrayCollection();
    }

    /**
     * Add socio
     *
     * @param \AppBundle\Entity\Socio $socio
     *
     * @return Pedido
     */
    public function addSocio(\AppBundle\Entity\Socio $socio)
    {
        $this->socio[] = $socio;

        return $this;
    }

    /**
     * Remove socio
     *
     * @param \AppBundle\Entity\Socio $socio
     */
    public function removeSocio(\AppBundle\Entity\Socio $socio)
    {
        $this->socio->removeElement($socio);
    }

    /**
     * Get socio
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSocio()
    {
        return $this->socio;
    }
}
