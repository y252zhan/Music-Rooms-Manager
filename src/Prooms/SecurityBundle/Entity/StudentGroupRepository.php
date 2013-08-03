<?php

namespace Prooms\SecurityBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Prooms\SecurityBundle\Entity\StudentGroup;
use Prooms\SecurityBundle\Entity\User;

class StudentGroupRepository extends EntityRepository
{
    public function findGroupByStudent(Student $student) {
        $studentGroup = $this->firstOrderFind($student);
        if( ! $studentGroup ) $studentGroup = $this->secondOrderFind1($student);
        if( ! $studentGroup ) $studentGroup = $this->secondOrderFind2($student);
        if( ! $studentGroup ) $studentGroup = $this->thirdOrderFind($student);
        if (! $studentGroup) return $this->findDefaultGroup();
        return $studentGroup;
    }
    
    private function firstOrderFind(Student $student) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $this->composeFindGroupBaseQuery($student, $qb);
        
        $qb->andWhere("sg.major = :major")
            ->andWhere("sg.year = :year")
            ->andWhere("sg.gpaFloor < :gpa")
            ->setParameters(array(
                'degree' => $student->getDegree(),
                'major' => $student->getMajor(),
                'year' => $student->getYear(),
                'gpa' => $student->getGpa()
            ))
            ->orderBy('sg.gpaFloor', 'DESC');
        
        $studentGroups = $qb->getQuery()->getResult();
        
        if(count($studentGroups)==0) return NULL;
        else return $studentGroups[0];
    }
    
    private function secondOrderFind1(Student $student) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $this->composeFindGroupBaseQuery($student, $qb);
        
        $qb->andWhere("sg.major is Null")
            ->andWhere("sg.year = :year")
            ->andWhere("sg.gpaFloor < :gpa")
            ->setParameters(array(
                'degree' => $student->getDegree(),
                'year' => $student->getYear(),
                'gpa' => $student->getGpa()
            ))
            ->orderBy('sg.gpaFloor', 'DESC');
        
        $studentGroups = $qb->getQuery()->getResult();
        
        if(count($studentGroups)==0) return NULL;
        else return $studentGroups[0];
    }
    
    private function secondOrderFind2(Student $student) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $this->composeFindGroupBaseQuery($student, $qb);
        
        $qb->andWhere("sg.major = :major")
            ->andWhere("sg.year is NULL")
            ->andWhere("sg.gpaFloor < :gpa")
            ->setParameters(array(
                'degree' => $student->getDegree(),
                'major' => $student->getMajor(),
                'gpa' => $student->getGpa()
            ))
            ->orderBy('sg.gpaFloor', 'DESC');
        
        $studentGroups = $qb->getQuery()->getResult();
        
        if(count($studentGroups)==0) return NULL;
        else return $studentGroups[0];
    }
    
    private function thirdOrderFind(Student $student) {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $this->composeFindGroupBaseQuery($student, $qb);
        
        $qb->andWhere("sg.major is NULL")
            ->andWhere("sg.year is NULL")
            ->andWhere("sg.gpaFloor < :gpa")
            ->setParameters(array(
                'degree' => $student->getDegree(),
                'gpa' => $student->getGpa()
            ))
            ->orderBy('sg.gpaFloor', 'DESC');
        
        $studentGroups = $qb->getQuery()->getResult();
        
        if(count($studentGroups)==0) return NULL;
        else return $studentGroups[0];
    }
    
    private function composeFindGroupBaseQuery(Student $student, $qb) {
        $qb->select('sg')
            ->from('ProomsSecurityBundle:StudentGroup', 'sg')
            ->where("sg.degree = :degree");
        
        if($student->getInstrument()=="Piano") {
            $qb->andWhere("sg.instrument = 'Piano'");
        }
        else {
            $qb->andWhere("sg.instrument = 'Other'");
        }
    }
    
    public function findDefaultGroup() {
        $qb = $this->getEntityManager()->createQueryBuilder();
        
        $qb->select('sg')
            ->from('ProomsSecurityBundle:StudentGroup', 'sg')
            ->where("sg.degree is NULL")
            ->andWhere("sg.instrument is NULL")
            ->andWhere("sg.major is NULL")
            ->andWhere("sg.year is NULL")
            ->andWhere("sg.gpaFloor is NULL");
            
        $studentGroup = $qb->getQuery()->getOneOrNullResult();
        return $studentGroup;
    }
}
