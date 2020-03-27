<?php


namespace FileTransferBundle\ParameterBag;


use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

class ParameterBag extends FrozenParameterBag
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->container->getParameterBag()->all();
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        return $this->container->getParameter($name);
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return $this->container->hasParameter($name);
    }
}
