<?php

namespace Modules\ItkPortalSubmit\Providers;

use App\Customer;
use App\Misc\Helper;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Modules\EndUserPortal\Http\Controllers\EndUserPortalController;
use Modules\EndUserPortal\Providers\EndUserPortalServiceProvider;
use Modules\ItkPortalSubmit\Http\Middleware\CheckPermission;
use App\Http\Middleware\VerifyCsrfToken;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Illuminate\Contracts\Encryption\Encrypter;

define('ITK_PORTAL_SUBMIT_MODULE', 'itkportalsubmit');
class ItkPortalSubmitServiceProvider extends ServiceProvider
{
    /**
     * Indicates if cookies should be serialized.
     *
     * @var bool
     */
    protected static bool $serialize = true;

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
      // Custom event listener for route.
      $events = app(Dispatcher::class);
      $events->listen(RouteMatched::class, function (RouteMatched $routeMatched) {

        // Check current route name.
        $routeName = $routeMatched->route->getName();
        if ($routeName === 'enduserportal.submit') {

          // Get auth data if available
          $auth_data = request()->cookie('enduserportal_auth');

          // Decrypt auth data.
          if ($auth_data) {
            try {
              $auth_data_decrypted = app(Encrypter::class)->decrypt($auth_data, false);
              [$customer_id, $hash] = explode('|', $auth_data_decrypted ?? '');
            } catch (\Exception $e) {
              app(Helper::class)->logException($e, 'ItkPortalSubmitServiceProvider');
              abort(403);
            }
          }

          // Make redirect if auth data is did not validate.
          if (empty($customer_id) || empty($hash)) {
            $this->redirectLogin($routeMatched);
          }
        }
      });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('itkportalsubmit.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'itkportalsubmit'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/itkportalsubmit');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/itkportalsubmit';
        }, \Config::get('view.paths')), [$sourcePath]), 'itkportalsubmit');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

  /**
   * Redirect to login page.
   *
   * @param \Illuminate\Routing\Events\RouteMatched $routeMatched
   *
   * @return void
   */
    private function redirectLogin(RouteMatched $routeMatched): void {
      $mailbox_id = $routeMatched->route->parameter('mailbox_id');
      redirect()->route('enduserportal.login', ['mailbox_id' => $mailbox_id])->send();
    }
}
