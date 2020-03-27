<?php


namespace FileTransferBundle\ParameterBag;


use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

class ParameterBag extends FrozenParameterBag
{
    /**
     * @var Symfony\Component\DependencyInjection\Container
     */
    private $container;

    public function __construct(ContainerInterface $container)
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
