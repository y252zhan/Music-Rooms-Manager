<?php
namespace Prooms\RoomsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="system_status")
 * @ORM\Entity(repositoryClass="Prooms\RoomsBundle\Entity\SystemStatusRepository")
 */
class SystemStatus
{
    /**
     * @ORM\Column(type="integer") 
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="system_status", type="boolean")
     */
    private $systemStatus;
    
    public function getId() {
        return $this->id;
    }
    
    public function getSystemStatus() {
        return $this->systemStatus;
    }
    
    public function setSystemStatus($status) {
        $this->systemStatus = $status;
    }
}

