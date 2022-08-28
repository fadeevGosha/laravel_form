<?php

namespace Yobidoyobi\Form;

use Yobidoyobi\Form\Extension\EloquentExtension;
use Yobidoyobi\Form\Extension\FormDefaultsTypeExtension;
use Yobidoyobi\Form\Extension\FormValidatorExtension;
use Yobidoyobi\Form\Extension\Http\HttpExtension;
use Yobidoyobi\Form\Extension\SessionExtension;
use Yobidoyobi\Form\Extension\Validation\ValidationTypeExtension;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\AbstractRendererEngine;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormRendererInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

class SymfonyFormProvider extends ServiceProvider
{
    protected bool $defer = false;

    /**
     * @throws BindingResolutionException
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/form.php';
        $this->publishes([$configPath => config_path('form.php')], 'config');

        $twig = $this->getTwigEnvironment();
        $loader = $twig->getLoader();

        if (!$loader instanceof ChainLoader) {
            $loader = new ChainLoader([$loader]);
            $twig->setLoader($loader);
        }

        $loader->addLoader(new FilesystemLoader($this->getTemplateDirectories()));

        $twig->addRuntimeLoader(new FactoryRuntimeLoader(array(
            FormRenderer::class => function () {
                return $this->app->make(FormRenderer::class);
            }
        )));

        $twig->addExtension(new FormExtension());

        $twig->addFilter(new TwigFilter('trans', function ($id = null) {
            if (empty($id)) {
                return '';
            }
            return app('translator')->get($id);
        }));

        $twig->addFunction(new TwigFunction('csrf_token', 'csrf_token'));

        $this->registerBladeDirectives();
        $this->registerViewComposer();
    }

    public function register():void
    {
        $configPath = __DIR__ . '/../config/form.php';
        $this->mergeConfigFrom($configPath, 'form');

        $this->app->singleton(TwigRendererEngine::class, function ($app) {
            $theme = (array) $app['config']->get('form.theme', 'bootstrap_5_layout.html.twig');
            return new TwigRendererEngine($theme, $this->getTwigEnvironment());
        });

        $this->app->singleton(FormRenderer::class, function ($app) {
            $renderer = $app->make(TwigRendererEngine::class);
            return new FormRenderer($renderer);
        });

        $this->app->alias(FormRenderer::class, FormRendererInterface::class);

        $this->app->bind('form.type.extensions', concrete: function ($app) {
            return [
                new FormDefaultsTypeExtension($app['config']->get('form.defaults', [])),
                new ValidationTypeExtension($app['validator']),
            ];
        });

        $this->app->bind('form.type.guessers', function ($app) {
            return [];
        });

        $this->app->bind(FormFactoryInterface::class, FormFactory::class);

        $this->app->bind('form.extensions', function ($app) {
            return [
                new SessionExtension(),
                new HttpExtension(),
                new EloquentExtension(),
                new FormValidatorExtension(),
            ];
        });

        $this->app->bind('form.resolved_type_factory', function () {
            return new ResolvedFormTypeFactory();
        });

        $this->app->singleton(FormFactory::class, function ($app) {
            return Forms::createFormFactoryBuilder()
                ->addExtensions($app['form.extensions'])
                ->addTypeExtensions($app['form.type.extensions'])
                ->addTypeGuessers($app['form.type.guessers'])
                ->addTypes(array(...$this->app->tagged('form.types')))
                ->setResolvedTypeFactory($app['form.resolved_type_factory'])
                ->getFormFactory();
        });

        $this->app->alias(FormFactory::class, 'form.factory');
        $this->app->alias(FormFactory::class, FormFactoryInterface::class);

        $this->app->bind(FormFactoryInterface::class, FormFactory::class);
        $this->app->bind(SluggerInterface::class, AsciiSlugger::class);
        $this->app->bind(FormRegistry::class, FormRegistryInterface::class);
    }

    protected function registerBladeDirectives()
    {
        Blade::directive('form', function ($expression) {
            return sprintf(
                '<?php echo \\%s::form(%s); ?>',
                FormRenderer::class,
                trim($expression, '()')
            );
        });

        foreach (['start', 'end', 'widget', 'errors', 'label', 'row', 'rest'] as $method) {
            $callable = function ($expression) use ($method) {
                return sprintf(
                    '<?php echo \\%s::%s(%s); ?>',
                    FormRenderer::class,
                    $method,
                    trim($expression, '()')
                );
            };
            Blade::directive('form_' . $method, $callable);
            Blade::directive('form' . ucfirst($method), $callable);
        }
    }

    protected function registerViewComposer()
    {
        $this->app['view']->composer('*', function ($view) {
            if ($view instanceof View) {
                foreach ($view->getData() as $key => $value) {
                    if ($value instanceof Form) {
                        $view->with($key, $value->createView());
                    }
                }
            }
        });
    }

    public function provides(): array
    {
        return [
            FormFactoryInterface::class,
            TwigRendererEngine::class,
            AbstractRendererEngine::class,
            FormRenderer::class,
            FormRendererInterface::class,
            FormRendererInterface::class,
            FormFactoryInterface::class,
            'form.factory',
            'form.extensions',
        ];
    }

    protected function getTemplateDirectories(): array
    {
        $reflected = new \ReflectionClass(FormExtension::class);
        $path = dirname($reflected->getFileName()) . '/../Resources/views/Form';

        $dirs = (array)$this->app['config']->get('form.template_directories', []);
        return array_merge([$path], $dirs);
    }


    /**
     * @return Environment
     * @throws BindingResolutionException
     */
    protected function getTwigEnvironment(): Environment
    {
        if (! $this->app->bound(Environment::class)) {
            $this->app->singleton(Environment::class, function () {
                return new Environment(new ChainLoader([]), [
                    'cache' => storage_path('framework/views/twig'),
                ]);
            });
        }

        /** @var Environment $twig */
        return $this->app->make(Environment::class);
    }
}
