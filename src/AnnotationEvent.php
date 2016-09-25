<?php
namespace kuiper\annotations;

use Symfony\Component\EventDispatcher\Event;
use ReflectionClass;

class AnnotationEvent extends Event
{
    /**
     * @var ReflectionClass 
     */
    private $class;

    /**
     * @var array
     */
    private $annotations;

    public function __construct(ReflectionClass $class)
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getAnnotations()
    {
        return $this->annotations;
    }

    public function setAnnotations(array $annotations)
    {
        $this->annotations = $annotations;
        return $this;
    }
}
