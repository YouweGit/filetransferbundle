<?php


namespace FileTransferBundle\ParameterBag;


use Psr\Container\ContainerInterface;
use Scheb\TwoFactorBundle\Security\Authentication\RememberMe\RememberMeServicesDecorator;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

class ParameterBagFactory
{
    public function __invoke(ContainerInterface $container)
    {
        if (class_exists('\Symfony\Component\DependencyInjection\ParameterBag\ContainerBag')) {
            $bag = new \Symfony\Component\DependencyInjection\ParameterBag\ContainerBag($container);
        } else {
            $bag = $container->get(ParameterBag::class);
        }

        return $bag;
    }

}
