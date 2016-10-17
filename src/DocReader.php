<?php
namespace kuiper\annotations;

use ReflectionProperty;
use ReflectionMethod;
use ReflectionClass;
use InvalidArgumentException;
use kuiper\reflection\ReflectionFileFactoryInterface;
use kuiper\reflection\ReflectionFileFactory;
use kuiper\reflection\ReflectionType;
use kuiper\reflection\FqcnResolver;
use kuiper\annotations\exception\ClassNotFoundException;

class DocReader implements DocReaderInterface
{
    /**
     * @var ReflectionFileFactoryInterface
     */
    private $reflectionFileFactory;
    
    public function __construct(ReflectionFileFactoryInterface $reflFileFactory = null)
    {
        $this->reflectionFileFactory = $reflFileFactory ?: ReflectionFileFactory::createInstance();
        DocParser::checkDocReadability();
    }

    /**
     * @inheritDoc
     */
    public function getPropertyType(ReflectionProperty $property)
    {
        return $this->parseAnnotationType($property->getDocComment(), $property->getDeclaringClass(), 'var');
    }

    /**
     * @inheritDoc
     */
    public function getPropertyClass(ReflectionProperty $property)
    {
        return $this->getClassType($this->getPropertyType($property));
    }

    /**
     * @inheritDoc
     */
    public function getParameterTypes(ReflectionMethod $method)
    {
        $parameters = [];
        foreach ($method->getParameters() as $parameter) {
            if (method_exists($parameter, 'hasType')
                && $parameter->hasType()) {
                // detected from php 7.0 ReflectionType
                $parameters[$parameter->getName()]
                    = ReflectionType::fromReflectionType($parameter->getType());
            } else {
                $parameters[$parameter->getName()] = ReflectionType::mixed();
            }
        }
        $re = '/@param\s+(\S+)\s+\$([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';
        if (!preg_match_all($re, $this->getMethodDocComment($method), $matches)) {
            return $parameters;
        }
        $declaringClass = $method->getDeclaringClass();
        foreach ($matches[2] as $index => $name) {
            if (!isset($parameters[$name])) {
                continue;
            }
            if ($parameters[$name]->isMixed()) {
                // if type is unknown, parse from doc block param tag
                $parameters[$name] = $this->parseType($matches[1][$index], $declaringClass);
            }
        }
        return $parameters;
    }

    /**
     * @inheritDoc
     */
    public function getParameterClasses(ReflectionMethod $method)
    {
        $parameters = [];
        foreach ($this->getParameterTypes($method) as $name => $type) {
            $parameters[$name] = $this->getClassType($type);
        }
        return array_filter($parameters);
    }

    /**
     * @inheritDoc
     */
    public function getReturnType(ReflectionMethod $method)
    {
        if (method_exists($method, 'hasReturnType')
            && $method->hasReturnType()) {
            // detected from php 7.0 ReflectionType
            $type = ReflectionType::fromReflectionType($method->getReturnType());
            if (!$type->isMixed()) {
                return $type;
            }
        }
        return $this->parseAnnotationType($this->getMethodDocComment($method), $method->getDeclaringClass(), 'return');
    }

    /**
     * @inheritDoc
     */
    public function getReturnClass(ReflectionMethod $method)
    {
        return $this->getClassType($this->getReturnType($method));
    }

    protected function getClassType(ReflectionType $type)
    {
        return $type->isObject() ? $type->getClassName() : null;
    }

    protected function parseAnnotationType($docBlock, ReflectionClass $declaringClass, $annotationName)
    {
        if (!preg_match('/@'.$annotationName.'\s+(\S+)/', $docBlock, $matches)) {
            return ReflectionType::mixed();
        }
        return $this->parseType($matches[1], $declaringClass);
    }

    /**
     * Parses the type
     *
     * @param string $type
     * @param ReflectionClass $declaringClass
     * @return ReflectionType
     */
    protected function parseType($type, ReflectionClass $declaringClass)
    {
        if (empty($type)) {
            throw new InvalidArgumentException("type cannot be empty");
        } elseif (!is_string($type)) {
            throw new InvalidArgumentException("type should be string, got " . ReflectionType::describe($type));
        }
        if (in_array($type, ['self', 'static'])) {
            return ReflectionType::objectType($declaringClass->getName());
        }
        try {
            $type = ReflectionType::parse($type);
            return $this->resolveFqcn($type, $declaringClass);
        } catch (InvalidArgumentException $e) {
            trigger_error("Parse type error: " . $e->getMessage());
            return ReflectionType::mixed();
        }
    }

    protected function resolveFqcn(ReflectionType $type, ReflectionClass $declaringClass)
    {
        if ($type->isObject()) {
            $name = $type->getClassName();
            if (isset($name) && $name[0] !== '\\') {
                $reflFile = $this->reflectionFileFactory->create($declaringClass->getFilename());
                $resolver = new FqcnResolver($reflFile);
                $fqcn = $resolver->resolve($name, $declaringClass->getNamespaceName());
                return ReflectionType::objectType($fqcn);
            } else {
                return $type;
            }
        } elseif ($type->isArray()) {
            return ReflectionType::arrayType($this->resolveFqcn($type->getArrayValueType(), $declaringClass));
        } elseif ($type->isCompound()) {
            $types = [];
            foreach ($type->getCompoundTypes() as $compoundType) {
                $types[] = $this->resolveFqcn($compoundType, $declaringClass);
            }
            return ReflectionType::compoundType($types);
        } else {
            return $type;
        }
    }

    protected function getMethodDocComment(ReflectionMethod $method)
    {
        $doc = $method->getDocComment();
        $name = $method->getName();
        if (stripos($doc, '@inheritdoc') !== false) {
            $class = $method->getDeclaringClass();
            if (false !== ($parent = $class->getParentClass())) {
                if ($parent->hasMethod($name)) {
                    return $this->getMethodDocComment($parent->getMethod($name));
                }
            }
            foreach ($class->getInterfaces() as $interface) {
                if ($interface->hasMethod($name)) {
                    return $interface->getMethod($name)->getDocComment();
                }
            }
        } else {
            return $doc;
        }
    }
}
