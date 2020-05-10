<?php

namespace Melanth\Routing;

use ReflectionFunctionAbstract;
use Melanth\Support\Arr;

trait RouteDependencyResolver
{
    /**
     * Inject the dependencies into the given method.
     *
     * @param \ReflectionFunctionAbstract $reflector  The abstract reflection instance.
     * @param array                       $parameters The routing parameters.
     *
     * @return array
     */
    protected function resolveMethodDependencies(ReflectionFunctionAbstract $reflector, array $parameters) : array
    {
        $dependencies = array_values($parameters);

        foreach ($reflector->getParameters() as $key => $parameter) {
            $class = $parameter->getClass();

            if ($class && ! $this->findDependency($class->name, $dependencies)) {
                $dependency = $parameter->isDefaultValueAvailable()
                    ? $parameter->getDefaultValue()
                    : $this->container->make($class->name);

                $this->spliceParameter($dependencies, $key, $dependency);
            }
            elseif (! isset($parameters[$parameter->getName()]) && $parameter->isDefaultValueAvailable()) {
                $this->spliceParameter($dependencies, $key, $parameter->getDefaultValue());
            }
        }

        return $dependencies;
    }

    /**
     * Determine whether the dependencies contain the class instance.
     *
     * @param string $classname    The class name.
     * @param array  $dependencies The resource dependencies.
     *
     * @return bool
     */
    protected function findDependency(string $classname, array $dependencies) : bool
    {
        return ! is_null(Arr::first($dependencies, function ($dependency) use ($classname) {
            return $dependency instanceof $classname;
        }));
    }

    /**
     * Append a value into the parameters.
     *
     * @param array $parameters The parameters.
     * @param int   $offset     The target offset.
     * @param mixed $value      The target value.
     *
     * @return void
     */
    protected function spliceParameter(array &$parameters, int $offset, $value) : void
    {
        array_splice($parameters, $offset, 0, [$value]);
    }
}
