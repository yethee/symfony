<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle;

use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddConstraintValidatorsPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddValidatorInitializersPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddConsoleCommandPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\FormPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\TemplatingPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\RoutingResolverPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\ProfilerPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\TranslatorPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\LoggingTranslatorPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddCacheWarmerPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddCacheClearerPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\AddExpressionLanguageProvidersPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\ContainerBuilderDebugDumpPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\CompilerDebugDumpPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\TranslationExtractorPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\TranslationDumperPass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\Compiler\SerializerPass;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\DependencyInjection\FragmentRendererPass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Bundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class FrameworkBundle extends Bundle
{
    public function boot()
    {
        ErrorHandler::register(null, false)->throwAt($this->container->getParameter('debug.error_handler.throw_at'), true);

        if ($trustedProxies = $this->container->getParameter('kernel.trusted_proxies')) {
            Request::setTrustedProxies($trustedProxies);
        }

        if ($this->container->getParameter('kernel.http_method_override')) {
            Request::enableHttpMethodParameterOverride();
        }

        if ($trustedHosts = $this->container->getParameter('kernel.trusted_hosts')) {
            Request::setTrustedHosts($trustedHosts);
        }
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RoutingResolverPass());
        $container->addCompilerPass(new ProfilerPass());
        // must be registered before removing private services as some might be listeners/subscribers
        // but as late as possible to get resolved parameters
        $container->addCompilerPass(new RegisterListenersPass(), PassConfig::TYPE_BEFORE_REMOVING);
        $container->addCompilerPass(new TemplatingPass());
        $container->addCompilerPass(new AddConstraintValidatorsPass());
        $container->addCompilerPass(new AddValidatorInitializersPass());
        $container->addCompilerPass(new AddConsoleCommandPass());
        $container->addCompilerPass(new FormPass());
        $container->addCompilerPass(new TranslatorPass());
        $container->addCompilerPass(new LoggingTranslatorPass());
        $container->addCompilerPass(new AddCacheWarmerPass());
        $container->addCompilerPass(new AddCacheClearerPass());
        $container->addCompilerPass(new AddExpressionLanguageProvidersPass());
        $container->addCompilerPass(new TranslationExtractorPass());
        $container->addCompilerPass(new TranslationDumperPass());
        $container->addCompilerPass(new FragmentRendererPass(), PassConfig::TYPE_AFTER_REMOVING);
        $container->addCompilerPass(new SerializerPass());

        if ($container->getParameter('kernel.debug')) {
            $container->addCompilerPass(new ContainerBuilderDebugDumpPass(), PassConfig::TYPE_AFTER_REMOVING);
            $container->addCompilerPass(new CompilerDebugDumpPass(), PassConfig::TYPE_AFTER_REMOVING);
        }
    }
}
